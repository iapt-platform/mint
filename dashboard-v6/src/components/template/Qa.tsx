import { Collapse } from "antd";

import DiscussionTopic from "../discussion/DiscussionTopic";
import type { TResType } from "../../api/discussion";

interface IQaCtl {
  id: string;
  title?: string;
  resId?: string;
  resType?: TResType;
}
const QaCtl = ({ id, title, resType }: IQaCtl) => {
  return (
    <Collapse bordered={false}>
      <Collapse.Panel header={title} key="1">
        {resType ? (
          <DiscussionTopic resType={resType} topicId={id} hideTitle hideReply />
        ) : (
          "resType error"
        )}
      </Collapse.Panel>
    </Collapse>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IQaCtl;
  console.log(prop);
  return (
    <>
      <QaCtl {...prop} />
    </>
  );
};

export default Widget;
