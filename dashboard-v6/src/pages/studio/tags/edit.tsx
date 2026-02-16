import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import { Card } from "antd";

import GoBack from "../../../components/studio/GoBack";
import TagCreate from "../../../components/tag/TagCreate";

const Widget = () => {
  const intl = useIntl();
  const { studioname, tagId } = useParams(); //url 参数

  return (
    <Card
      title={
        <GoBack
          to={`/studio/${studioname}/tags/list`}
          title={intl.formatMessage({ id: "labels.tag.list" })}
        />
      }
    >
      <TagCreate studio={studioname} tagId={tagId} />
    </Card>
  );
};

export default Widget;
