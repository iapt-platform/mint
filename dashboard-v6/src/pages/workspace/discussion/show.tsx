import { useParams } from "react-router";
import { Divider, Result } from "antd";

import Discussion from "../../../components/discussion/Discussion";
import DiscussionAnchor from "../../../components/discussion/DiscussionAnchor";
import { asDiscussionTarget, type TResType } from "../../../api/discussion";

/**
 * 某个对象上的全部讨论：`/workspace/discussion/show/:type/:id`。
 *
 * 上面是讨论挂靠的内容（句子 / 文章），下面是话题列表，点开在原地看话题与回复
 * （模板话题也在原地新建）。讨论抽屉右上角的「新窗口打开」链到这里。
 * 与话题页一样在 workspace 里（要登录），能看到什么由后端的可见性规则决定。
 */
const Widget = () => {
  const { type, id } = useParams();
  const target = asDiscussionTarget(type as TResType);

  if (!target || !id) {
    return <Result status="404" title="不支持的讨论对象" />;
  }

  return (
    <div style={{ maxWidth: 960, margin: "0 auto", padding: 16 }}>
      <title>discussion</title>
      <DiscussionAnchor resId={id} resType={target} />
      <Divider />
      <Discussion resId={id} resType={target} />
    </div>
  );
};

export default Widget;
