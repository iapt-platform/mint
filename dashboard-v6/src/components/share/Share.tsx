import { Divider } from "antd";
import { useState } from "react";

import Collaborator from "./Collaborator";
import CollaboratorAdd from "./CollaboratorAdd";
import type { EResType } from "./utils";

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
