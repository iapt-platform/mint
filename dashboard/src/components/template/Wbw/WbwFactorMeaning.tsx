import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return <div>{data.factorMeaning?.value}</div>;
};

export default Widget;
