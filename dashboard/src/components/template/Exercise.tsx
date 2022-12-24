import { Button } from "antd";

interface IWidgetExerciseCtl {
  id?: string;
  channel?: string;
  children?: React.ReactNode;
}
const ExerciseCtl = ({ id, channel, children }: IWidgetExerciseCtl) => {
  return (
    <>
      <div>{children}</div>
      <Button type="primary">做练习</Button>
    </>
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
