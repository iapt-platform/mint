import { Button } from "antd";
import { TagOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { courseInfo } from "../../reducers/current-course";
import { currentUser } from "../../reducers/current-user";
import TagsManager from "./TagsManager";

interface IWidget {
  resId?: string;
  resType?: string;
  disabled?: boolean;
  trigger?: React.ReactNode;
  onSelect?: Function;
  onCreate?: Function;
  onOpen?: Function;
}

const TagSelectButtonWidget = ({
  resId,
  resType,
  disabled = false,
  trigger,
  onSelect,
  onCreate,
  onOpen,
}: IWidget) => {
  const course = useAppSelector(courseInfo);
  const user = useAppSelector(currentUser);

  const studioName =
    course?.course?.studio?.realName ?? user?.nickName ?? undefined;

  console.debug("TagSelectButton studioName", studioName);

  return (
    <TagsManager
      studioName={studioName}
      resId={resId}
      resType={resType}
      trigger={
        trigger ?? (
          <Button
            disabled={disabled}
            type="text"
            icon={
              <TagOutlined
                onClick={() => {
                  if (typeof onOpen !== "undefined") {
                    onOpen();
                  }
                }}
              />
            }
          />
        )
      }
    />
  );
};

export default TagSelectButtonWidget;
