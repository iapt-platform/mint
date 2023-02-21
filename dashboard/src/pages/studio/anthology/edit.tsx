import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Card, Tabs } from "antd";

import GoBack from "../../../components/studio/GoBack";

import TocTree from "../../../components/anthology/TocTree";
import AnthologyInfoEdit from "../../../components/article/AnthologyInfoEdit";

const Widget = () => {
  const intl = useIntl();
  const [title, setTitle] = useState("");
  const { studioname, anthology_id } = useParams(); //url 参数

  return (
    <>
      <Card
        title={
          <GoBack to={`/studio/${studioname}/anthology/list`} title={title} />
        }
      >
        <Tabs
          items={[
            {
              key: "info",
              label: intl.formatMessage({ id: "buttons.basic.information" }),
              children: (
                <AnthologyInfoEdit
                  anthologyId={anthology_id}
                  onTitleChange={(title: string) => {
                    setTitle(title);
                  }}
                />
              ),
            },
            {
              key: "toc",
              label: `目录`,
              children: <TocTree anthologyId={anthology_id} />,
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;
