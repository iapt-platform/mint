import { Dropdown, type MenuProps, message, Tag, Typography } from "antd";
import {
  ATaskCategory,
  type ITaskData,
  type ITaskResponse,
  type ITaskUpdateRequest,
  type TTaskCategory,
} from "../../api/task";
import { useIntl } from "react-intl";
import { patch } from "../../request";

const { Text } = Typography;

interface IWidget {
  task?: ITaskData;
  onChange?: (data: ITaskData[]) => void;
}
const Category = ({ task, onChange }: IWidget) => {
  const intl = useIntl();

  const placeholder = intl.formatMessage({ id: "labels.task.category" });

  interface IMenu {
    key: TTaskCategory;
    label: string;
  }
  const items: MenuProps["items"] = ATaskCategory.map((item) => {
    const value: IMenu = {
      key: item,
      label: intl.formatMessage({
        id: `labels.task.category.${item}`,
      }),
    };
    return value;
  });

  const onClick: MenuProps["onClick"] = (e) => {
    if (!task) {
      console.error("no task");
      return;
    }

    const setting: ITaskUpdateRequest = {
      id: task.id,
      studio_name: "",
      category: e.key as TTaskCategory,
    };
    const url = `/v2/task/${task.id}`;
    console.info("api request", url, setting);
    patch<ITaskUpdateRequest, ITaskResponse>(url, setting).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        message.success("Success");
        onChange && onChange([json.data]);
      } else {
        message.error(json.message);
      }
    });
  };
  return (
    <Dropdown menu={{ items, onClick }}>
      <Tag>
        <Text type={task?.category ? "success" : "secondary"}>
          {intl.formatMessage({
            id: `labels.task.category.${task?.category}`,
            defaultMessage: placeholder,
          })}
        </Text>
      </Tag>
    </Dropdown>
  );
};

export default Category;
