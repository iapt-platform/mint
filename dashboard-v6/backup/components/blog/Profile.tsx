import { Card } from "antd";

const ProfileWidget = () => {
  return (
    <>
      <Card title="简介" bordered={false} style={{ width: "100%" }}>
        <p>Card content</p>
        <p>Card content</p>
        <p>Card content</p>
      </Card>
      <Card title="团队" bordered={false} style={{ width: "100%" }}>
        <p>Card content</p>
        <p>Card content</p>
        <p>Card content</p>
      </Card>
    </>
  );
};

export default ProfileWidget;
