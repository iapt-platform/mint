import { Button, Card } from "antd";

interface IWidgetExerciseCtl {
  id?: string;
  title?: string;
  channel?: string;
  children?: React.ReactNode;
}
const ExerciseCtl = ({ id, title, channel, children }: IWidgetExerciseCtl) => {
  const cardTitle = title ? title : "练习";
  return (
    <Card
      title={cardTitle}
      extra={<Button type="primary">做练习</Button>}
      style={{ backgroundColor: "wheat" }}
    >
      {children}
    </Card>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const Widget = ({ props, children }: IWidget) => {
  const prop: IWidgetExerciseCtl = JSON.parse(atob(props));
  return <ExerciseCtl {...prop}>{children}</ExerciseCtl>;
};

export default Widget;
