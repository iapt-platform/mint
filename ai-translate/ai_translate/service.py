import json
import time
import logging
from typing import List, Dict, Any, Optional
from datetime import datetime
from dataclasses import dataclass
import time

import requests

from .utils import is_stopped

logger = logging.getLogger(__name__)


class DatabaseException(Exception):
    def __init__(self, message="database api access exception"):
        self.message = message
        super().__init__(self.message)


class SectionTimeout(Exception):
    def __init__(self, message="片段超时"):
        self.message = message
        super().__init__(self.message)


class TaskFailException(Exception):
    def __init__(self, message="task fail"):
        self.message = message
        super().__init__(self.message)


class LLMFailException(Exception):
    def __init__(self, message="LLM request fail"):
        self.message = message
        super().__init__(self.message)


@dataclass
class TaskProgress:
    """任务进度"""
    current: int
    total: int


@dataclass
class TaskInfo:
    """任务模型"""
    id: str
    title: str
    category: str
    description: str
    status: str


@dataclass
class Task:
    """任务模型"""
    info: TaskInfo
    progress: TaskProgress


@dataclass
class AiModel:
    """AI模型配置"""
    uid: str
    model: str
    url: str
    key: str
    token: str
    system_prompt: Optional[str] = None


@dataclass
class Sentence:
    """句子模型"""
    book_id: str
    paragraph: int
    word_start: int
    word_end: int
    channel_uid: str
    content: str
    content_type: str = 'markdown'
    access_token: Optional[str] = None


@dataclass
class Payload:
    """消息模型"""
    model: AiModel
    task: Task
    prompt: str
    sentence: Sentence


@dataclass
class Message:
    """消息模型"""
    model: AiModel
    task: Task
    payload: List[Payload]
    sentence: Sentence


class AiTranslateService:
    """AI翻译服务"""

    def __init__(self, redis, ch, method, api_url, customer_timeout):
        self.queue = 'ai_translate'
        self.model_token = None
        self.task = None
        self.redis_clusters = redis[0]
        self.redis_namespace = redis[1]
        self.api_timeout = 100
        self.llm_timeout = 300
        self.task_topic_id = None
        self.api_url = api_url
        self.customer_timeout = customer_timeout
        self.channel = ch
        self.maxProcessTime = 15 * 60  # 一个句子的最大处理时间

    def process_translate(self, message_id: str, body: Message) -> bool:
        """处理翻译任务"""
        is_stopped()
        taskStartAt = int(time.time())

        self.task = body.task

        self.redis_clusters.set(
            f"{self.redis_namespace}/task/{self.task.id}/message_id", message_id)
        pointer_key = f"{self.redis_namespace}/task/{message_id}/pointer"
        pointer = 0

        if self.redis_clusters.exists(pointer_key):
            # 回到上次中断的点
            pointer = int(self.redis_clusters.get(pointer_key))
            logger.info(f"last break point {pointer}")
            if pointer >= len(body.payload):
                self.redis_clusters.delete(pointer_key)
                return True

        # 获取model token
        self.model_token = body.model.token

        self._set_task_status(self.task.id, 'running')

        # 设置task discussion topic
        taskTopicKey = f'{self.redis_namespace}/message/{message_id}/topic'
        if self.redis_clusters.exists(taskTopicKey):
            # 获取上次的task topic id
            self.task_topic_id = self.redis_clusters.get(taskTopicKey)
        else:
            self.task_topic_id = self._task_discussion(
                self.task.id,
                'task',
                self.task.title,
                f'id:{message_id}',
                None
            )
        times = [self.maxProcessTime]
        # breakpoint()
        for i in range(pointer, len(body.payload)):
            is_stopped()

            startAt = int(time.time())

            message = body.payload[i]
            task_discussion_content = []

            # 推理

            response_llm = self._request_llm(message)
            task_discussion_content.append('- LLM request successful')

            if self.task.category == 'translate':
                # 写入句子库
                message.sentence.content = response_llm['content']
                self._save_sentence(message.sentence)

            if self.task.category == 'suggest':
                # 写入pr
                self._save_pr(message.sentence, response_llm['content'])

            # 获取句子id
            s_uid = self._get_sentence_id(message.sentence)

            # 写入句子 discussion
            topic_children = []
            # 任务结果
            topic_children.append(response_llm['content'])
            # 推理过程写入discussion
            if response_llm.get('reasoningContent'):
                topic_children.append(response_llm['reasoningContent'])
            self._sentence_discussion(s_uid, message.prompt, topic_children)

            # 修改task 完成度
            progress = self._set_task_progress(
                TaskProgress(i + 1, len(body.payload)))
            task_discussion_content.append(f"- progress={progress}")

            # 写入task discussion
            if self.task_topic_id:
                content = '\n'.join(task_discussion_content)
                d_id = self._task_discussion(
                    self.task.id,
                    'task',
                    self.task.title,
                    content,
                    self.task_topic_id
                )
            else:
                logger.error('no task discussion root')

            if i + 1 < len(body.payload):
                self.redis_clusters.set(pointer_key, i+1)
                # 计算本次时间和剩余时间
                # breakpoint()
                onceTime = int(time.time())-startAt
                times.append(onceTime)
                times.sort(reverse=True)
                # 取出第一个元素
                maxTime = times[0]
                # 计算剩余时间
                remain = self.customer_timeout-(int(time.time())-taskStartAt)
                if remain < maxTime:
                    # 时间不足
                    raise SectionTimeout
        # 任务完成 修改任务状态为 done
        self._set_task_status(self.task.id, 'done')
        self.redis_clusters.delete(pointer_key)
        logger.info('ai translate task complete')
        return True

    def _sentence_discussion(self, id, prompt, discussions):
        topic_id = self._task_discussion(
            id,
            'sentence',
            self.task.title,
            prompt,
            None
        )

        if topic_id:
            logger.info(f'{self.queue} discussion create topic successful')

            for content in discussions:
                logger.debug(f'{self.queue} discussion child request')
                d_id = self._task_discussion(
                    id, 'sentence', self.task.title, content, topic_id)
                if d_id:
                    logger.info(
                        f'{self.queue} discussion child successful')
        else:
            logger.error(
                f'{self.queue} discussion create topic response is null')

    def _set_task_status(self, task_id: str, status: str):
        """设置任务状态"""
        url = f"{self.api_url}/v2/task-status/{task_id}"
        data = {'status': status}
        logger.debug(f'ai_translate task status request: {url}, data: {data}')

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.patch(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if response.ok:
            logger.info(f'ai_translate task status successful ({status})')
        else:
            logger.error(
                f'ai_translate task status update fail. response: {response.text}')

    def _save_model_log(self, token: str, data: Dict[str, Any]) -> bool:
        """保存模型日志"""
        url = f"{self.api_url}/v2/model-log"

        headers = {'Authorization': f'Bearer {token}'}
        response = requests.post(
            url, json=data, headers=headers, timeout=self.api_timeout)
        # breakpoint()
        if not response.ok:
            logger.error(
                f'ai-translate model log create failed: {response.json()}')
            return False
        return True

    def _task_discussion(self, res_id: str, res_type: str, title: str, content: str, parent_id: Optional[str] = None):
        """创建任务讨论"""
        url = f"{self.api_url}/v2/discussion"

        task_discussion_data = {
            'res_id': res_id,
            'res_type': res_type,
            'content': content,
            'content_type': 'markdown',
            'type': 'discussion',
            'notification': False,
        }

        if parent_id:
            task_discussion_data['parent'] = parent_id
        else:
            task_discussion_data['title'] = title

        logger.debug(
            f'{self.queue} discussion create: {url}, data: {json.dumps(task_discussion_data)}')

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.post(
            url, json=task_discussion_data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(
                f'{self.queue} discussion create error: {response.json()}')
            return False

        logger.debug(
            f'{self.queue} discussion create: {json.dumps(response.json())}')

        response_data = response.json()
        if response_data.get('data', {}).get('id'):
            return response_data['data']['id']
        return False

    def _request_llm(self, message: Message) -> Dict[str, Any]:
        """请求LLM"""
        param = {
            "model": message.model.model,
            "messages": [
                {"role": "system", "content": message.model.system_prompt or ''},
                {"role": "user", "content": message.prompt},
            ],
            "temperature": 0.7,
            "stream": False
        }

        logger.info(
            f'{self.queue} LLM request {message.model.url} model: {param["model"]}')

        # logger.debug(
        #     f'{self.queue} LLM api request: {message.model.url}, data: {json.dumps(param)}')

        # 写入 model log
        model_log_data = {
            'model_id': message.model.uid,
            'request_at': datetime.now().isoformat(),
            'request_data': json.dumps(param, ensure_ascii=False),
        }

        # 失败重试
        max_retries = 3
        attempt = 0

        headers = {'Authorization': f'Bearer {message.model.key}'}

        while attempt < max_retries:
            try:
                response = requests.post(
                    message.model.url,
                    json=param,
                    headers=headers,
                    timeout=self.llm_timeout
                )
                response.raise_for_status()

                logger.info(f'{self.queue} LLM request successful')

                model_log_data.update({
                    'request_headers': json.dumps(dict(response.request.headers), ensure_ascii=False),
                    'response_headers': json.dumps(dict(response.headers), ensure_ascii=False),
                    'status': response.status_code,
                    'response_data': json.dumps(response.json(), ensure_ascii=False),
                    'success': True
                })
                break
            except requests.exceptions.RequestException as e:
                model_log_data.update({
                    'response_headers': json.dumps(dict(e.response.request.headers), ensure_ascii=False),
                    'response_headers': json.dumps(dict(e.response.headers), ensure_ascii=False),
                    'status': e.response.status_code,
                    'response_data': json.dumps(e.response.json(), ensure_ascii=False),
                    'success': False
                })
                attempt += 1
                status = getattr(e.response, 'status_code',
                                 0) if hasattr(e, 'response') else 0

                # 某些错误不需要重试
                if status in [400, 401, 403, 404, 422]:
                    logger.warning(f"客户端错误，不重试: {status}")
                    raise LLMFailException

                # 服务器错误或网络错误可以重试
                if attempt < max_retries:
                    delay = 2 ** attempt  # 指数退避
                    logger.warning(f"请求失败（第 {attempt} 次），{delay} 秒后重试...")
                    time.sleep(delay)
                else:
                    logger.error("达到最大重试次数，请求最终失败")
                    raise e
            except Exception as e:
                raise e
            finally:
                try:
                    self._save_model_log(self.model_token, model_log_data)
                    logger.info(f'{self.queue} model log saved')
                except Exception as e:
                    logger.error(e)

        ai_data = response.json()
        # logger.debug(f'{self.queue} LLM http response: {response.json()}')

        response_content = ai_data['choices'][0]['message']['content']
        reasoning_content = ai_data['choices'][0]['message'].get(
            'reasoning_content')

        output = {'content': response_content}
        logger.debug(f'{self.queue} LLM response content={response_content}')

        if not reasoning_content:
            logger.debug(f'{self.queue} no reasoningContent')
        else:
            logger.debug(f'{self.queue} reasoning={reasoning_content}')
            output['reasoningContent'] = reasoning_content

        return output

    def _save_sentence(self, sentence: Sentence):
        """写入句子库"""
        url = f"{self.api_url}/v2/sentence"

        logger.info(f"{self.queue} sentence update {url}")

        headers = {'Authorization': f'Bearer {self.model_token}'}
        data = {'sentences': [sentence.__dict__]}

        response = requests.post(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(
                f'{self.queue} sentence update failed: {url}, data: {response.json()}')
            raise DatabaseException("sentence 数据库写入错误")

        count = response.json()['data']['count']
        logger.info(f"{self.queue} sentence update {count} successful")

    def _save_pr(self, sentence: Sentence, content: str):
        """保存PR"""
        url = f"{self.api_url}/v2/sentpr"
        logger.info(f"{self.queue} sentence update {url}")

        data = {
            'book': sentence.book_id,
            'para': sentence.paragraph,
            'begin': sentence.word_start,
            'end': sentence.word_end,
            'channel': sentence.channel_uid,
            'text': content,
            'notification': False,
            'webhook': False,
        }

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.post(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(
                f'{self.queue} sentence update failed: {url}, data: {response.json()}')
            raise DatabaseException("pr 数据库写入错误")

        if response.json().get('ok'):
            logger.info(f"{self.queue} sentence suggest update successful")
        else:
            logger.error(
                f"{self.queue} sentence suggest update failed: {url}, data: {response.json()}")

    def _get_sentence_id(self, sentence: Sentence) -> str:
        """获取句子ID"""
        url = f"{self.api_url}/v2/sentence-info/aa"
        logger.info(f'ai translate: {url}')

        params = {
            'book': sentence.book_id,
            'par': sentence.paragraph,
            'start': sentence.word_start,
            'end': sentence.word_end,
            'channel': sentence.channel_uid
        }

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.get(
            url, params=params, headers=headers, timeout=self.api_timeout)

        if not response.json().get('ok'):
            logger.error(f'{self.queue} sentence id error: {response.json()}')
            return False

        s_uid = response.json()['data']['id']
        logger.debug(f"sentence id={s_uid}")
        return s_uid

    def _set_task_progress(self, current: TaskProgress) -> int:
        """设置任务进度"""
        if current.total > 0:
            progress = int(current.current * 100 / current.total)
        else:
            progress = 100
            logger.error(
                f'{self.queue} progress total is zero, task_id: {self.task.id}')

        url = f"{self.api_url}/v2/task/{self.task.id}"
        data = {'progress': progress}

        logger.debug(
            f'{self.queue} task progress request: {url}, data: {data}')

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.patch(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(
                f'{self.queue} task progress error: {response.json()}')
        else:
            logger.info(
                f'{self.queue} task progress successful progress={response.json()["data"]["progress"]}')

        return progress

    def handle_failed_translate(self, message_id: str, translate_data: List[Any], exception: Exception):
        """处理失败的翻译任务"""
        try:
            # 彻底失败时的业务逻辑
            # 设置task为失败状态
            self._set_task_status(self.task.id, 'stop')

            # 将故障信息写入task discussion
            if self.task_topic_id:
                error_message = f"**处理失败ai任务时出错** 请重启任务 message id={message_id} 错误信息：{str(exception)}"
                d_id = self._task_discussion(
                    self.task.id,
                    'task',
                    self.task.title,
                    error_message,
                    self.task_topic_id
                )
        except Exception as e:
            logger.error(f'处理失败ai任务时出错: {str(e)}')
