import { Popover } from "antd";
import { IWbw } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  return (
    <div>
      <Popover
        content={
          <div style={{ width: 500 }}>
            <WbwMeaningSelect
              data={data}
              onSelect={(e: string) => {
                if (typeof onChange !== "undefined") {
                  onChange(e);
                }
              }}
            />
          </div>
        }
        placement="bottomLeft"
        trigger="hover"
      >
        {data.meaning?.value}
      </Popover>
    </div>
  );
};

export default Widget;
