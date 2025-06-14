import json
import time
import logging
import requests
from typing import List, Dict, Any, Optional
from datetime import datetime
import redis
from requests.exceptions import RequestException
from dataclasses import dataclass
from abc import ABC, abstractmethod

# 配置日志
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class DatabaseException(Exception):
    """数据库异常"""
    pass


@dataclass
class TaskProgress:
    """任务进度"""
    current: int
    total: int


@dataclass
class Task:
    """任务模型"""
    id: str
    title: str
    category: str
    description: str
    status: str


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
class Message:
    """消息模型"""
    model: AiModel
    task: Task
    prompt: str
    sentence: Sentence


class RedisClusters:
    """Redis集群工具类"""

    def __init__(self, host='localhost', port=6379, db=0):
        self.redis_client = redis.Redis(host=host, port=port, db=db)

    def put(self, key: str, value: Any, ttl: Optional[int] = None):
        """存储数据"""
        if isinstance(value, (dict, list)):
            value = json.dumps(value)
        if ttl:
            self.redis_client.setex(key, ttl, value)
        else:
            self.redis_client.set(key, value)

    def get(self, key: str) -> Any:
        """获取数据"""
        value = self.redis_client.get(key)
        if value:
            return value.decode('utf-8')
        return None

    def has(self, key: str) -> bool:
        """检查键是否存在"""
        return self.redis_client.exists(key) > 0

    def forget(self, key: str):
        """删除键"""
        self.redis_client.delete(key)


class JobInterface(ABC):
    """作业接口"""

    @abstractmethod
    def is_stop(self) -> bool:
        """检查作业是否停止"""
        pass


class AiTranslateService:
    """AI翻译服务"""

    def __init__(self, app_url: str = "http://localhost:8000"):
        self.queue = 'ai_translate'
        self.model_token = None
        self.task = None
        self.redis_clusters = RedisClusters()
        self.mq = RabbitMQService()
        self.api_timeout = 100
        self.llm_timeout = 300
        self.task_topic_id = None
        self.app_url = app_url

    def process_translate(self, message_id: str, messages: List[Message], job: JobInterface) -> bool:
        """处理翻译任务"""

        if not messages or len(messages) == 0:
            logger.error('message is not array')
            return False

        first = messages[0]
        self.task = first.task
        task_id = self.task.id

        self.redis_clusters.put(f"/task/{task_id}/message_id", message_id)
        pointer_key = f"/task/{task_id}/pointer"
        pointer = 0

        if self.redis_clusters.has(pointer_key):
            # 回到上次中断的点
            pointer = int(self.redis_clusters.get(pointer_key))
            logger.info(f"last break point {pointer}")

        # 获取model token
        self.model_token = first.model.token
        logger.debug(f'{self.queue} ai assistant token: {self.model_token}')

        self._set_task_status(self.task.id, 'running')

        # 设置task discussion topic
        self.task_topic_id = self._task_discussion(
            self.task.id,
            'task',
            self.task.title,
            self.task.category,
            None
        )

        for i in range(pointer, len(messages)):
            # 获取当前内存使用量（Python版本的内存监控）
            try:
                import psutil
                process = psutil.Process()
                memory_info = process.memory_info()
                logger.debug(
                    f"memory usage: {memory_info.rss / 1024 / 1024:.2f} MB")
            except ImportError:
                logger.debug(
                    "psutil not installed, skipping memory monitoring")

            if job.is_stop():
                logger.info(f"收到退出信号 pointer={i}")
                return False

            # 检测停止标记的工具函数需要实现
            # if Tools.is_stop():
            #     return False

            self.redis_clusters.put(pointer_key, i)
            message = messages[i]
            task_discussion_content = []

            # 推理
            try:
                response_llm = self._request_llm(message)
                task_discussion_content.append('- LLM request successful')
            except RequestException as e:
                raise e

            if self.task.category == 'translate':
                # 写入句子库
                message.sentence.content = response_llm['content']
                try:
                    self._save_sentence(message.sentence)
                except Exception as e:
                    logger.error(f'sentence error: {e}')
                    continue

            if self.task.category == 'suggest':
                # 写入pr
                try:
                    self._save_pr(message.sentence, response_llm['content'])
                except Exception as e:
                    logger.error(f'sentence error: {e}')
                    continue

            # 获取句子id
            s_uid = self._get_sentence_id(message.sentence)

            # 写入句子 discussion
            topic_id = self._task_discussion(
                s_uid,
                'sentence',
                self.task.title,
                self.task.category,
                None
            )

            if topic_id:
                logger.info(f'{self.queue} discussion create topic successful')
                topic_children = []
                # 提示词
                topic_children.append(message.prompt)
                # 任务结果
                topic_children.append(response_llm['content'])
                # 推理过程写入discussion
                if response_llm.get('reasoningContent'):
                    topic_children.append(response_llm['reasoningContent'])

                for content in topic_children:
                    logger.debug(f'{self.queue} discussion child request')
                    d_id = self._task_discussion(
                        s_uid, 'sentence', self.task.title, content, topic_id)
                    if d_id:
                        logger.info(
                            f'{self.queue} discussion child successful')
            else:
                logger.error(
                    f'{self.queue} discussion create topic response is null')

            # 修改task 完成度
            progress = self._set_task_progress(
                TaskProgress(i + 1, len(messages)))
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

        # 任务完成 修改任务状态为 done
        if i + 1 == len(messages):
            self._set_task_status(self.task.id, 'done')

        self.redis_clusters.forget(pointer_key)
        logger.info('ai translate task complete')
        return True

    def _set_task_status(self, task_id: str, status: str):
        """设置任务状态"""
        url = f"{self.app_url}/api/v2/task-status/{task_id}"
        data = {'status': status}

        logger.debug(f'ai_translate task status request: {url}, data: {data}')

        headers = {'Authorization': f'Bearer {self.model_token}'}
        response = requests.patch(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(f'ai_translate task status error: {response.json()}')
        else:
            logger.info('ai_translate task status done')

    def _save_model_log(self, token: str, data: Dict[str, Any]) -> bool:
        """保存模型日志"""
        url = f"{self.app_url}/api/v2/model-log"

        headers = {'Authorization': f'Bearer {token}'}
        response = requests.post(
            url, json=data, headers=headers, timeout=self.api_timeout)

        if not response.ok:
            logger.error(
                f'ai-translate model log create failed: {response.json()}')
            return False
        return True

    def _task_discussion(self, res_id: str, res_type: str, title: str, content: str, parent_id: Optional[str] = None):
        """创建任务讨论"""
        url = f"{self.app_url}/api/v2/discussion"

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
        logger.debug(
            f'{self.queue} LLM api request: {message.model.url}, data: {json.dumps(param)}')

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
                    'response_headers': json.dumps(dict(response.headers), ensure_ascii=False),
                    'status': response.status_code,
                    'response_data': json.dumps(response.json(), ensure_ascii=False),
                    'success': True
                })
                self._save_model_log(self.model_token, model_log_data)
                break

            except requests.exceptions.RequestException as e:
                attempt += 1
                status = getattr(e.response, 'status_code',
                                 0) if hasattr(e, 'response') else 0

                # 某些错误不需要重试
                if status in [400, 401, 403, 404, 422]:
                    logger.warning(f"客户端错误，不重试: {status}")
                    raise e

                # 服务器错误或网络错误可以重试
                if attempt < max_retries:
                    delay = 2 ** attempt  # 指数退避
                    logger.warning(f"请求失败（第 {attempt} 次），{delay} 秒后重试...")
                    time.sleep(delay)
                else:
                    logger.error("达到最大重试次数，请求最终失败")
                    raise e

        logger.info(f'{self.queue} model log saved')

        ai_data = response.json()
        logger.debug(f'{self.queue} LLM http response: {response.json()}')

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
        url = f"{self.app_url}/api/v2/sentence"

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
        url = f"{self.app_url}/api/v2/sentpr"
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
        url = f"{self.app_url}/api/v2/sentence-info/aa"
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

        url = f"{self.app_url}/api/v2/task/{self.task.id}"
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
