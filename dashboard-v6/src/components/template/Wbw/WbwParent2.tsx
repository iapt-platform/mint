import { Tag, Tooltip } from "antd";
import { useIntl } from "react-intl";
import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const WbwParent2Widget = ({ data }: IWidget) => {
  const intl = useIntl();

  return data.grammar2?.value ? (
    data.grammar2.value.trim() !== "" ? (
      <Tooltip title={data.parent2?.value}>
        <Tag color={"yellow"}>
          {intl.formatMessage({
            id:
              "dict.fields.type." +
              data.grammar2.value?.replaceAll(".", "") +
              ".short.label",
            defaultMessage: data.grammar2.value?.replaceAll(".", ""),
          })}
        </Tag>
      </Tooltip>
    ) : (
      <></>
    )
  ) : (
    <></>
  );
};

export default WbwParent2Widget;
