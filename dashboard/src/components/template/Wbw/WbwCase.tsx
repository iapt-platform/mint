import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return (
    <div>
      {data.type?.value}-{data.grammar?.value}
    </div>
  );
};

export default Widget;
