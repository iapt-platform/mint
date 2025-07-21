import logging

from .service import AiTranslateService, SectionTimeout, LLMFailException, Message
from .decode_dataclass import ns_to_dataclass
from .utils import is_stopped

logger = logging.getLogger(__name__)


def handle_message(redis, ch, method, id, content_type, body, api_url: str, openai_proxy: str, customer_timeout: int, worker_name: str):
    MaxRetry: int = 3
    try:
        logger.info("process message start (%s) messages", len(body.payload))
        consumer = AiTranslateService(
            redis, ch, method, api_url, openai_proxy, customer_timeout, worker_name)
        messages = ns_to_dataclass([body], Message)
        consumer.process_translate(id, messages[0])
        logger.info(f'message {id} ack')
        ch.basic_ack(delivery_tag=method.delivery_tag)  # 确认消息
    except SectionTimeout as e:
        # 时间到了，活还没干完 NACK 并重新入队
        logger.warning(
            f'time is not enough for complete current message id={id}. requeued')
        ch.basic_nack(delivery_tag=method.delivery_tag, requeue=True)
    except LLMFailException as e:
        errMsg = f'message {id} LLM Fail'
        logger.warning(errMsg)
        consumer.handle_failed(id, errMsg, e)
        ch.basic_nack(delivery_tag=method.delivery_tag,
                      requeue=False)
    except Exception as e:
        logger.error(f"error: {e}")
        logger.exception("Exception")
        # retry
        retryKey = f'{redis[1]}/message/retry/{id}'
        retry = int(redis[0].get(retryKey)
                    or 0) if redis[0].exists(retryKey) else 0
        if retry >= MaxRetry:
            errMsg = f'超过最大重试次数[{MaxRetry}]，任务失败 id={id}'
            logger.warning(errMsg)
            consumer.handle_failed(id, errMsg, e)
            # NACK 丢弃或者进入死信队列
            ch.basic_nack(delivery_tag=method.delivery_tag,
                          requeue=False)
        else:
            retry = retry+1
            redis[0].set(retryKey, retry)
            # NACK 并重新入队
            errMsg = f'消息处理错误，重新压入队列 [{retry}/{MaxRetry}]'
            logger.warning(errMsg)
            consumer.handle_retry(id, errMsg, e)
            ch.basic_nack(delivery_tag=method.delivery_tag, requeue=True)
    finally:
        is_stopped()
