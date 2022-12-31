import { Card } from "antd";
import { useState } from "react";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import GoBack from "../../../components/studio/GoBack";

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("Loading");

  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/course/list`} title={title} />}
    ></Card>
  );
};

export default Widget;
