import { Avatar, Col, Row } from "antd";
import { useState } from "react";
import { IUser } from "../auth/User";
import CommentShow from "./CommentShow";
import CommentEdit from "./CommentEdit";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: string;
  user: IUser;
  parent?: string;
  title?: string;
  content?: string;
  children?: IComment[];
  childrenCount?: number;
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  onSelect?: Function;
  onCreated?: Function;
}
const Widget = ({ data, onSelect, onCreated }: IWidget) => {
  const [edit, setEdit] = useState(false);
  console.log(data);
  return (
    <Row>
      <Col flex={"2em"} style={{ padding: 8 }}>
        <Avatar>{data.user?.nickName?.slice(0, 1)}</Avatar>
      </Col>
      <Col flex={"auto"}>
        {edit ? (
          <CommentEdit
            data={data}
            onCreated={(e: IComment) => {
              if (typeof onCreated !== "undefined") {
                onCreated(e);
              }
            }}
          />
        ) : (
          <CommentShow
            data={data}
            onEdit={() => {
              setEdit(true);
            }}
          />
        )}
      </Col>
    </Row>
  );
};

export default Widget;
