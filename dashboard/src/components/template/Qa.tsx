import { Collapse } from "antd";

import DiscussionTopic from "../discussion/DiscussionTopic";
import { TResType } from "../discussion/DiscussionListCard";

const { Panel } = Collapse;

interface IQaCtl {
  id: string;
  title?: string;
  resId?: string;
  resType?: TResType;
}
const QaCtl = ({ id, title, resId, resType }: IQaCtl) => {
  return (
    <Collapse bordered={false}>
      <Panel header={title} key="1">
        {resType ? (
          <DiscussionTopic resType={resType} topicId={id} hideTitle hideReply />
        ) : (
          "resType error"
        )}
      </Panel>
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
