import { Tooltip } from "antd";
import { IWbw } from "./WbwWord";
import { Dict2SvgIcon, DictIcon } from "../../../assets/icon";

interface IWidget {
  data: IWbw;
}
const WbwParentIcon = ({ data }: IWidget) => {
  return data.parent?.value ? (
    data.parent.value.trim() !== "" ? (
      <Tooltip title={data.parent?.value}>
        <Dict2SvgIcon />
      </Tooltip>
    ) : (
      <></>
    )
  ) : (
    <></>
  );
};

export default WbwParentIcon;
