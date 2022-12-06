import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return <div>{data.meaning?.value}</div>;
};

export default Widget;
