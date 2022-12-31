import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Button, Card } from "antd";

import GoBack from "../../../components/studio/GoBack";

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  useEffect(() => {
    setTitle("title");
  }, [courseId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/course/list`} title={title} />}
      extra={
        <Button type="link" danger>
          {intl.formatMessage({ id: "buttons.group.exit" })}
        </Button>
      }
    ></Card>
  );
};

export default Widget;
