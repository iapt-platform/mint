import logging
import ai_translate
import os
import sys
import logging
from typing import List
from ai_translate import (
    AiTranslateService,
    JobInterface
)

logger = logging.getLogger(__name__)


class MockJob(JobInterface):
    """模拟作业类，用于测试"""

    def __init__(self):
        self._stop = False

    def is_stop(self) -> bool:
        """检查作业是否停止"""
        return self._stop

    def stop(self):
        """停止作业"""
        self._stop = True


def handle_message(message_id, content_type, body):
    logger.info(f"process message start len:{len(body)}")
    try:
        # 创建服务实例
        service = AiTranslateService(app_url="http://localhost:8000")

        # 创建作业实例
        job = MockJob()

        # 处理翻译任务
        result = service.process_translate(message_id, body, job)

        if result:
            logger.info("翻译任务完成成功!")
        else:
            logger.error("翻译任务失败!")

        return result

    except Exception as e:
        logger.error(f"测试过程中发生错误: {str(e)}")
