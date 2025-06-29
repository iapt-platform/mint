import logging

from .service import AiTranslateService, SectionTimeout, Message
from .decode_dataclass import ns_to_dataclass
from .utils import is_stopped

logger = logging.getLogger(__name__)


class TaskFailException(Exception):
    def __init__(self, message="task fail"):
        self.message = message
        super().__init__(self.message)


def handle_message(redis, ch, method, id, content_type, body, api_url: str, customer_timeout: int):
    MaxRetry: int = 3
    try:
        logger.info("process message start (%s) messages", len(body.payload))
        consumer = AiTranslateService(
            redis, ch, method, api_url, customer_timeout)
        messages = ns_to_dataclass([body], Message)
        consumer.process_translate(id, messages[0])
        ch.basic_ack(delivery_tag=method.delivery_tag)  # 确认消息
    except SectionTimeout as e:
        # 时间到了，活还没干完 NACK 并重新入队
        ch.basic_nack(delivery_tag=method.delivery_tag, requeue=True)
    except Exception as e:
        # retry
        retryKey = f'{redis[1]}/message/retry/{id}'
        retry: int = 0
        if redis[0].exists(retryKey):
            retry = redis[0].get(retryKey)
        if retry > MaxRetry:
            logger.error(f'超过最大重试次数[{MaxRetry}]，任务失败')
            # NACK 丢弃或者进入死信队列
            ch.basic_nack(delivery_tag=method.delivery_tag,
                          requeue=False)
            raise TaskFailException
        retry = retry+1
        redis[0].set(retryKey, retry)
        # NACK 并重新入队
        logger.warning(f'消息处理错误，重新压入队列 [{retry}/{MaxRetry}]')
        ch.basic_nack(delivery_tag=method.delivery_tag,
                      requeue=True)
        logger.error(f"error: {e}")
        logger.exception("发生异常")
    finally:
        is_stopped()
