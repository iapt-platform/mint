import { Button, message } from "antd";
import { TagOutlined } from "@ant-design/icons";

import TagSelect from "./TagSelect";
import { ITagData, ITagMapRequest, ITagMapResponseList } from "../api/Tag";
import { useAppSelector } from "../../hooks";
import { courseInfo } from "../../reducers/current-course";
import { currentUser } from "../../reducers/current-user";
import { post } from "../../request";
import { useIntl } from "react-intl";

interface IWidget {
  resId?: string;
  resType?: string;
  disabled?: boolean;
  onSelect?: Function;
  onCreate?: Function;
  onOpen?: Function;
}

const TagSelectButtonWidget = ({
  resId,
  resType,
  disabled = false,
  onSelect,
  onCreate,
  onOpen,
}: IWidget) => {
  const intl = useIntl();
  const course = useAppSelector(courseInfo);
  const user = useAppSelector(currentUser);

  const studioName =
    course?.course?.studio?.realName ?? user?.nickName ?? undefined;

  return (
    <TagSelect
      studioName={studioName}
      trigger={
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
      }
      onSelect={(tag: ITagData) => {
        if (typeof onSelect !== "undefined") {
          onSelect(tag);
        } else {
          if (studioName || course) {
            const data: ITagMapRequest = {
              table_name: resType,
              anchor_id: resId,
              tag_id: tag.id,
              course: course ? course.courseId : undefined,
              studio: studioName,
            };

            const url = `/v2/tag-map`;
            console.info("tag-map  api request", url, data);
            post<ITagMapRequest, ITagMapResponseList>(url, data)
              .then((json) => {
                console.info("tag-map api response", json);
                if (json.ok) {
                  message.success(
                    intl.formatMessage({ id: "flashes.success" })
                  );
                  if (typeof onCreate !== "undefined") {
                    onCreate(json.data.rows);
                  }
                } else {
                  message.error(json.message);
                }
              })
              .catch((e) => console.error(e));
          } else {
            console.error("no studio");
          }
        }
      }}
    />
  );
};

export default TagSelectButtonWidget;
