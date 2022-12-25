import { Button, Card } from "antd";

interface IWidgetExerciseCtl {
  id?: string;
  channel?: string;
  children?: React.ReactNode;
}
const ExerciseCtl = ({ id, channel, children }: IWidgetExerciseCtl) => {
  return (
    <Card
      title="练习"
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
