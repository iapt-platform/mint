import { Card } from "antd";
import { useState } from "react";

import { IComment } from "./CommentItem";
import CommentList from "./CommentList";

const defaultData: IComment[] = Array(5)
  .fill(3)
  .map((item, id) => {
    return {
      id: "dd",
      content: "评论内容",
      title: "评论标题" + id,
      user: {
        id: "string",
        nickName: "Visuddhinanda",
        realName: "Visuddhinanda",
        avatar: "",
      },
    };
  });
interface IWidget {
  resId: string;
  onSelect?: Function;
}
const Widget = ({ resId, onSelect }: IWidget) => {
  const [data, setData] = useState<IComment[]>(defaultData);

  return (
    <div>
      <Card title="问题列表" extra={<a href="#">More</a>}>
        <CommentList
          onSelect={(e: React.MouseEvent<HTMLSpanElement, MouseEvent>) => {
            if (typeof onSelect !== "undefined") {
              onSelect(e);
            }
          }}
          data={data}
        />
      </Card>
    </div>
  );
};

export default Widget;
