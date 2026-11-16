import { Tag, Tooltip } from "antd";
import { useIntl } from "react-intl";
import type { IFrom } from "../../api/relation";

/** 「拼写 + 语法标记」的只读展示（关系表、语尾表的 from / to 列）。 */
const GrammarTags = ({ value }: { value?: IFrom | null }) => {
  const intl = useIntl();
  return (
    <>
      {value?.spell}
      {value?.case?.map((item) => (
        <Tooltip title={item} key={item}>
          <Tag>{intl.formatMessage({ id: `dict.fields.type.${item}.label`, defaultMessage: item })}</Tag>
        </Tooltip>
      ))}
    </>
  );
};

export default GrammarTags;
