import { Button } from "antd";
import { TagOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { courseInfo } from "../../reducers/current-course";
import { currentUser } from "../../reducers/current-user";
import TagsManager from "./TagsManager";
import type { ITagMapData } from "../../api/tag";

interface IWidget {
  resId?: string;
  resType?: string;
  disabled?: boolean;
  selectorTitle?: React.ReactNode;
  trigger?: React.ReactNode;
  onOpen?: () => void;
  onCreate?: (tags: ITagMapData[]) => void;
}

const TagSelectButtonWidget = ({
  resId,
  resType,
  disabled = false,
  selectorTitle,
  trigger,
  onOpen,
}: IWidget) => {
  const course = useAppSelector(courseInfo);
  const user = useAppSelector(currentUser);

  const studioName =
    course?.course?.studio?.realName ?? user?.nickName ?? undefined;

  return (
    <TagsManager
      title={selectorTitle}
      studioName={studioName}
      courseId={course?.courseId}
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
