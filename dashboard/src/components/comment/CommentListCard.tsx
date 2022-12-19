import { Card } from "antd";
import { useState } from "react";

import { IComment } from "./CommentItem";
import CommentList from "./CommentList";

interface IWidget {
  resId: string;
}
const Widget = ({ resId }: IWidget) => {
  const [data, setData] = useState<IComment[]>([]);

  return (
    <div>
      <Card title="问题列表" extra={<a href="#">More</a>}>
        <CommentList data={data} />
      </Card>
    </div>
  );
};

export default Widget;
