import { Button, Checkbox, Flex, Typography, theme } from "antd";
import { useIntl } from "react-intl";
import type { FacetBucket } from "../../api/tipitaka-search";
import {
  LAYER_OPTIONS,
  PITAKA_OPTIONS,
  isLayerCategory,
  isOtherCategory,
  isPitakaCategory,
  type ICategoryOption,
} from "./categories";

const { Text } = Typography;

interface IOption {
  key: string;
  label: string;
  count?: number;
}

interface IGroupProps {
  title: string;
  options: IOption[];
  selected: string[];
  onToggle: (key: string) => void;
}

const FacetGroup = ({ title, options, selected, onToggle }: IGroupProps) => {
  const { token } = theme.useToken();
  if (options.length === 0) {
    return null;
  }
  return (
    <div>
      <Text
        type="secondary"
        style={{ fontSize: token.fontSizeSM, letterSpacing: "0.04em" }}
      >
        {title}
      </Text>
      <Flex vertical gap={2} style={{ marginTop: 4 }}>
        {options.map((o) => (
          <Checkbox
            key={o.key}
            checked={selected.includes(o.key)}
            onChange={() => onToggle(o.key)}
            style={{ opacity: o.count === 0 ? 0.45 : 1 }}
          >
            <Flex justify="space-between" gap={8} style={{ width: 176 }}>
              <Text ellipsis={{ tooltip: o.label }}>{o.label}</Text>
              <Text
                type="secondary"
                style={{ fontVariantNumeric: "tabular-nums" }}
              >
                {o.count?.toLocaleString() ?? ""}
              </Text>
            </Flex>
          </Checkbox>
        ))}
      </Flex>
    </div>
  );
};

interface IWidget {
  /** meta.facets.category：当前查询条件下各标签的段落数（后端只给前若干个） */
  buckets: FacetBucket[];
  selected: string[];
  onChange: (category: string[]) => void;
}

/**
 * 全文检索的分类筛选：把 category 分面拆成「层级 / 藏别 / 其他分类」三组。
 *
 * 选中后作为 category 过滤重新查询，计数随之按新条件重算。
 * 层级、藏别组内单选（后端的 category 是「全部命中」，同组选两个必然为空），
 * 其他分类可多选。后端只返回计数最多的若干个标签，不在其中的选项不显示数字。
 */
const CategoryFacets = ({ buckets, selected, onChange }: IWidget) => {
  const intl = useIntl();
  const count = new Map(buckets.map((b) => [b.key, b.count]));

  const fixed = (list: ICategoryOption[]): IOption[] =>
    list.map((o) => ({
      key: o.key,
      label: `${intl.formatMessage({ id: o.labelId })} ${o.key}`,
      count: count.get(o.key),
    }));

  // 其他分类：分面里出现的，加上已选但这次分面里没有的（保证能取消）
  const otherKeys = [
    ...buckets.map((b) => b.key).filter(isOtherCategory),
    ...selected.filter((k) => isOtherCategory(k) && !count.has(k)),
  ];
  const others: IOption[] = otherKeys.map((key) => ({
    key,
    label: key,
    count: count.get(key),
  }));

  /** 层级、藏别：组内单选，再点一次取消 */
  const toggleSingle = (key: string, inGroup: (k: string) => boolean) => {
    if (selected.includes(key)) {
      onChange(selected.filter((k) => k !== key));
    } else {
      onChange([...selected.filter((k) => !inGroup(k)), key]);
    }
  };
  const toggleMulti = (key: string) =>
    onChange(
      selected.includes(key)
        ? selected.filter((k) => k !== key)
        : [...selected, key],
    );

  return (
    <Flex vertical gap={18}>
      <FacetGroup
        title={intl.formatMessage({ id: "search.facet.layer" })}
        options={fixed(LAYER_OPTIONS)}
        selected={selected}
        onToggle={(k) => toggleSingle(k, isLayerCategory)}
      />
      <FacetGroup
        title={intl.formatMessage({ id: "search.facet.pitaka" })}
        options={fixed(PITAKA_OPTIONS)}
        selected={selected}
        onToggle={(k) => toggleSingle(k, isPitakaCategory)}
      />
      <FacetGroup
        title={intl.formatMessage({ id: "search.facet.other" })}
        options={others}
        selected={selected}
        onToggle={toggleMulti}
      />
      {selected.length > 0 && (
        <Button
          size="small"
          type="link"
          style={{ alignSelf: "flex-start", padding: 0 }}
          onClick={() => onChange([])}
        >
          {intl.formatMessage({ id: "search.facet.clear" })}
        </Button>
      )}
    </Flex>
  );
};

export default CategoryFacets;
