import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Button, Card } from "antd";
import { Col, Row } from "antd";

import { get } from "../../../request";
import { IGroupResponse } from "../../../components/api/Group";
import GroupFile from "../../../components/group/GroupFile";
import GroupMember from "../../../components/group/GroupMember";
import GoBack from "../../../components/studio/GoBack";

const Widget = () => {
  const intl = useIntl();
  const { studioname, groupId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  useEffect(() => {
    get<IGroupResponse>(`/v2/group/${groupId}`).then((json) => {
      setTitle(json.data.name);
      document.title = `${json.data.name}`;
    });
  }, [groupId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/group/list`} title={title} />}
      extra={
        <Button type="link" danger>
          {intl.formatMessage({ id: "buttons.group.exit" })}
        </Button>
      }
    >
      <Row>
        <Col flex="auto" style={{ paddingRight: 10 }}>
          <GroupFile groupId={groupId} />
        </Col>
        <Col flex="380px">
          <GroupMember groupId={groupId} />
        </Col>
      </Row>
    </Card>
  );
};

export default Widget;
