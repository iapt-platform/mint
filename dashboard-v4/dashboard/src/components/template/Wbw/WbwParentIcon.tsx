import { Tooltip } from "antd";
import { IWbw } from "./WbwWord";
import { Dict2SvgIcon } from "../../../assets/icon";
import { errorClass } from "./WbwMeaning";

interface IWidget {
  data: IWbw;
  answer?: IWbw;
}
const WbwParentIcon = ({ data, answer }: IWidget) => {
  const iconEmpty = answer?.parent ? <Dict2SvgIcon /> : <></>;
  let title: string | null | undefined = "空";
  if (typeof data.parent?.value === "string" && data.parent?.value.length > 0) {
    title = data.parent?.value;
  }
  const icon = data.parent?.value ? (
    data.parent.value.trim() !== "" ? (
      <Tooltip title={title}>
        <Dict2SvgIcon />
      </Tooltip>
    ) : (
      iconEmpty
    )
  ) : (
    iconEmpty
  );

  const errClass = answer
    ? errorClass("parent", data.parent?.value, answer?.parent?.value)
    : "";
  return (
    <span className={"wbw_word_item" + errClass}>
      <span className="icon">{icon}</span>
    </span>
  );
};

export default WbwParentIcon;
