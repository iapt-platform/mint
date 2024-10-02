import { Divider } from "antd";
import { useState } from "react";

import Collaborator from "./Collaborator";
import CollaboratorAdd from "./CollaboratorAdd";

/**
 * - 1 PCS 文档
- 2 Channel 版本
- 3 Article 文章
- 4 Collection 文集
- 5 版本片段
 */
export enum EResType {
  pcs = 1,
  channel = 2,
  article = 3,
  collection = 4,
}
interface IWidget {
  resId: string;
  resType: EResType;
}
const ShareWidget = ({ resId, resType }: IWidget) => {
  const [reload, setReload] = useState(false);
  return (
    <div>
      <CollaboratorAdd
        resId={resId}
        resType={resType}
        onSuccess={() => {
          setReload(true);
        }}
      />
      <Divider></Divider>
      <Collaborator
        resId={resId}
        load={reload}
        onReload={() => {
          setReload(false);
        }}
      />
    </div>
  );
};

export default ShareWidget;
