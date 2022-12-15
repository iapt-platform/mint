import { useParams } from "react-router-dom";
import { Button, Card } from "antd";
import { Col, Row } from "antd";
import GroupFile from "../../../components/group/GroupFile";
import GroupMember from "../../../components/group/GroupMember";
import { useEffect, useState } from "react";
import { get } from "../../../request";
import { IGroupResponse } from "../../../components/api/Group";
import GoBack from "../../../components/studio/GoBack";

const Widget = () => {
  const { studioname, groupid } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  useEffect(() => {
    get<IGroupResponse>(`/v2/group/${groupid}`).then((json) => {
      setTitle(json.data.name);
    });
  }, [groupid]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/group/list`} title={title} />}
      extra={
        <Button type="link" danger>
          退群
        </Button>
      }
    >
      <Row>
        <Col flex="auto">
          <GroupFile groupid={groupid} />
        </Col>
        <Col flex="400px">
          <GroupMember groupid={groupid} />
        </Col>
      </Row>
    </Card>
  );
};

export default Widget;
