import { useEffect, useState } from "react";
import { Tabs } from "antd";

import { TResType } from "./DiscussionListCard";
import Discussion from "./Discussion";
import { get } from "../../request";
import QaBox from "./QaBox";

interface IInteractive {
  ok: boolean;
  data: ITypeData;
  message: string;
}

interface ITypeData {
  qa: IPower;
  help: IPower;
  discussion: IPower;
}

interface IPower {
  can_create: boolean;
  can_reply: boolean;
  count: number;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
}
const InteractiveAreaWidget = ({ resId, resType }: IWidget) => {
  const [showQa, setShowQa] = useState(false);
  const [qaCanEdit, setQaCanEdit] = useState(false);
  const [showHelp, setShowHelp] = useState(false);
  const [showDiscussion, setShowDiscussion] = useState(false);

  useEffect(() => {
    get<IInteractive>(`/v2/interactive/${resId}?res_type=${resType}`).then(
      (json) => {
        if (json.ok) {
          console.debug("interactive", json);
          if (json.data.qa.can_create || json.data.qa.can_reply) {
            setShowQa(true);
            setQaCanEdit(true);
          } else if (json.data.qa.count > 0) {
            setShowQa(true);
          } else {
            setShowQa(false);
          }

          if (json.data.help.can_create) {
            setShowHelp(true);
          } else if (json.data.help.can_reply) {
            if (json.data.help.count > 0) {
              setShowHelp(true);
            }
          } else {
            setShowHelp(false);
          }

          if (
            json.data.discussion.can_create ||
            json.data.discussion.can_reply ||
            json.data.discussion.count > 0
          ) {
            setShowDiscussion(true);
          } else {
            setShowDiscussion(false);
          }
        }
      }
    );
  }, [resId, resType]);

  return showQa || showHelp || showDiscussion ? (
    <Tabs
      size="small"
      items={[
        {
          label: `问答`,
          key: "qa",
          children: <QaBox resId={resId} resType={resType} />,
        },
        {
          label: `求助`,
          key: "help",
          children: <Discussion resId={resId} resType={resType} type="help" />,
        },
        {
          label: `讨论`,
          key: "discussion",
          children: (
            <Discussion resId={resId} resType={resType} type="discussion" />
          ),
        },
      ].filter((value) => {
        if (value.key === "qa") {
          return showQa;
        } else if (value.key === "help") {
          return showHelp;
        } else if (value.key === "discussion") {
          return showDiscussion;
        } else {
          return false;
        }
      })}
    />
  ) : (
    <></>
  );
};

export default InteractiveAreaWidget;
