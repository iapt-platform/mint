import dataclasses
from dataclasses import fields, is_dataclass
from typing import Type, get_type_hints, get_origin, get_args, Union, List, Dict, Any
from types import SimpleNamespace
# 方法1: 使用递归反射的通用解码器


def decode_dataclass(cls, data):
    """通用的dataclass解码器，支持嵌套结构"""
    if not is_dataclass(cls):
        return data

    if not isinstance(data, dict):
        raise ValueError(f"Expected dict for {cls.__name__}, got {type(data)}")

    # 获取类型提示
    type_hints = get_type_hints(cls)
    field_values = {}

    for field in fields(cls):
        field_name = field.name
        field_type = type_hints.get(field_name, field.type)

        if field_name not in data:
            if field.default != dataclasses.MISSING:
                field_values[field_name] = field.default
            elif field.default_factory != dataclasses.MISSING:
                field_values[field_name] = field.default_factory()
            else:
                raise ValueError(f"Missing required field: {field_name}")
            continue

        field_value = data[field_name]
        field_values[field_name] = _decode_field_value(field_type, field_value)
    output = cls(**field_values)
    return output


def _decode_field_value(field_type, value):
    """解码单个字段值"""
    # 处理None值
    if value is None:
        return None

    # 获取类型的origin（如List, Dict等）
    origin = get_origin(field_type)
    args = get_args(field_type)

    # 处理Optional类型 (Union[T, None])
    if origin is Union:
        # 检查是否是Optional类型
        if len(args) == 2 and type(None) in args:
            non_none_type = args[0] if args[1] is type(None) else args[1]
            return _decode_field_value(non_none_type, value)
        else:
            # 其他Union类型，尝试第一个类型
            return _decode_field_value(args[0], value)

    # 处理List类型
    if origin is list or origin is List:
        if not isinstance(value, list):
            raise ValueError(f"Expected list, got {type(value)}")
        element_type = args[0] if args else Any
        return [_decode_field_value(element_type, item) for item in value]

    # 处理Dict类型
    if origin is dict or origin is Dict:
        if not isinstance(value, dict):
            raise ValueError(f"Expected dict, got {type(value)}")
        value_type = args[1] if len(args) > 1 else Any
        return {k: _decode_field_value(value_type, v) for k, v in value.items()}

    # 处理dataclass类型
    if is_dataclass(field_type):
        return decode_dataclass(field_type, value)

    # 基础类型直接返回
    return value

# 方法4: 专门处理对象数组的函数


def decode_dataclass_list(cls, data_list):
    """将JSON对象数组解码为dataclass列表"""
    if not isinstance(data_list, list):
        raise ValueError(f"Expected list, got {type(data_list)}")

    return [decode_dataclass(cls, item) for item in data_list]


# 方法5: 扩展通用解码器支持顶层列表
def decode_json_to_type(target_type, json_data):
    """更通用的JSON解码器，支持顶层数组"""
    origin = get_origin(target_type)
    args = get_args(target_type)

    # 处理List[SomeDataClass]类型
    if origin is list or origin is List:
        if not isinstance(json_data, list):
            raise ValueError(
                f"Expected list for {target_type}, got {type(json_data)}")

        element_type = args[0] if args else Any
        return [decode_json_to_type(element_type, item) for item in json_data]

    # 处理单个dataclass
    if is_dataclass(target_type):
        return decode_dataclass(target_type, json_data)

    # 其他类型直接返回
    return json_data


def ns_to_dataclass(ns: Any, dataclass_type: Type[Any], field_mapping: Dict[str, str] = None) -> Any:
    """
    将 SimpleNamespace 对象转换为 dataclass 实例，支持嵌套结构和列表。

    Args:
        ns: 要转换的 SimpleNamespace 对象或其他值。
        dataclass_type: 目标 dataclass 类型。
        field_mapping: 可选的字段映射字典，键为 SimpleNamespace 属性名，值为 dataclass 字段名。

    Returns:
        转换后的 dataclass 实例或其他原始值。
    """
    if field_mapping is None:
        field_mapping = {}

    # 如果不是 SimpleNamespace，直接返回原始值
    if not isinstance(ns, SimpleNamespace):
        return ns

    # 获取 dataclass 的字段信息
    if not is_dataclass(dataclass_type) and not isinstance(dataclass_type, type):
        raise ValueError(f"{dataclass_type} 不是有效的 dataclass 类型")

    dc_fields = {f.name: f.type for f in fields(
        dataclass_type)} if is_dataclass(dataclass_type) else {}

    # 将 SimpleNamespace 转为字典
    ns_dict = vars(ns)
    result_dict = {}

    # 遍历 SimpleNamespace 的属性
    for ns_key, value in ns_dict.items():
        # 应用字段映射（如果有）
        dc_key = field_mapping.get(ns_key, ns_key)

        if dc_key not in dc_fields:
            continue  # 忽略 dataclass 中不存在的字段

        # 获取 dataclass 字段的类型
        field_type = dc_fields.get(dc_key)

        # 处理嵌套的 SimpleNamespace
        if isinstance(value, SimpleNamespace):
            result_dict[dc_key] = ns_to_dataclass(
                value, field_type, field_mapping)

        # 处理列表
        elif isinstance(value, list) and field_type:
            origin_type = get_origin(field_type)
            if origin_type is list:
                item_type = get_args(field_type)[
                    0] if get_args(field_type) else Any
                result_dict[dc_key] = [
                    ns_to_dataclass(item, item_type, field_mapping)
                    if isinstance(item, SimpleNamespace)
                    else item
                    for item in value
                ]
            else:
                result_dict[dc_key] = value

        # 直接赋值其他类型
        else:
            result_dict[dc_key] = value

    # 创建 dataclass 实例
    return dataclass_type(**result_dict)
