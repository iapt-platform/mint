import {
  DisplayWrapper,
  type IDisplayWrapperProps,
} from "../general/DisplayWrapper";
import TypePali from "../article/TypePali";

interface IWidget extends IDisplayWrapperProps {
  id?: string;
}
const ParaCtl = (props: IWidget) => {
  return (
    <DisplayWrapper {...props}>
      <TypePali type="para" id={props.id} />
    </DisplayWrapper>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  console.log(prop);
  return (
    <>
      <ParaCtl {...prop} />
    </>
  );
};

export default Widget;
