import { List, Select, Typography } from "antd";
import { useEffect, useState } from "react";
import { TeamOutlined, RobotOutlined } from "@ant-design/icons";

import { get } from "../../request";
import type {
  IApiResponseDictList,
  IDictFirstMeaningResponse,
  IFirstMeaning,
} from "../../api/dict";

const { Text, Link } = Typography;

interface IFactorInfo {
  factors: string;
  type: string;
  confidence: number;
}
interface IOptions {
  value: string;
  label: React.ReactNode;
}
interface IWidget {
  word?: string;
  add?: string;
  split?: string;
  onSearch?: (word: string, update?: boolean) => void;
}

const CompoundWidget = ({ word, add, onSearch }: IWidget) => {
  // compound 列表：由 word 异步拉取后更新
  const [compound, setCompound] = useState<IOptions[]>([]);
  // 用户手动选中的值（undefined 表示尚未手动选择，跟随自动推导）
  const [manualValue, setManualValue] = useState<string | undefined>(undefined);
  // 当前 word 的快照，用于判断 word 是否变化从而重置手动选择
  const [prevWord, setPrevWord] = useState<string | undefined>(word);
  const [meaningData, setMeaningData] = useState<IFirstMeaning[] | undefined>(
    undefined
  );

  // ── 派生 factors ──────────────────────────────────────────────
  const factors: IOptions[] =
    typeof add === "undefined"
      ? compound
      : [{ value: add, label: add }, ...compound];

  // ── 派生 currValue ────────────────────────────────────────────
  // 优先级：手动选中 > add prop > compound 第一项
  const defaultValue =
    typeof add !== "undefined"
      ? add
      : compound.length > 0
        ? compound[0].value
        : undefined;
  const currValue = manualValue ?? defaultValue;

  // ── word 变化时重置手动选择（render-phase derived state 模式）──
  if (word !== prevWord) {
    setPrevWord(word);
    setManualValue(undefined);
    setCompound([]);
    setMeaningData(undefined);
  }

  // ── 用户主动切换下拉 ──────────────────────────────────────────
  const onSelectChange = (value?: string) => {
    setManualValue(value);
    if (typeof value === "undefined") {
      setMeaningData(undefined);
      return;
    }
    const url =
      `/api/v2/dict-meaning?lang=zh-Hans&word=` + value.replaceAll("+", "-");
    console.info("dict compound url", url);
    get<IDictFirstMeaningResponse>(url).then((json) => {
      if (json.ok) {
        setMeaningData(json.data);
      }
    });
  };

  // ── currValue 变化时异步拉取释义（仅异步回调中 setState）────────
  useEffect(() => {
    if (typeof currValue === "undefined") {
      return;
    }
    const url =
      `/api/v2/dict-meaning?lang=zh-Hans&word=` +
      currValue.replaceAll("+", "-");
    console.info("dict compound url (auto)", url);
    let cancelled = false;
    get<IDictFirstMeaningResponse>(url).then((json) => {
      if (!cancelled && json.ok) {
        setMeaningData(json.data); // ✅ 异步回调内 setState，合规
      }
    });
    return () => {
      cancelled = true;
    };
  }, [currValue]);

  // ── word 变化时拉取 compound 列表（仅异步回调中 setState）───────
  useEffect(() => {
    if (typeof word === "undefined") {
      return; // reset 已在 render phase 处理
    }
    const url = `/api/v2/userdict?view=word&word=${word}`;
    console.info("dict compound url", url);
    let cancelled = false;
    get<IApiResponseDictList>(url).then((json) => {
      if (cancelled || !json.ok) return;

      const factorMap = new Map<string, IFactorInfo>();
      json.data.rows
        .filter((row) => typeof row.factors === "string")
        .forEach((row) => {
          let type = "";
          if (row.source?.includes("_USER")) type = "user";
          if (row.type === ".cp.") type = "robot";
          if (row.factors) {
            factorMap.set(row.factors, {
              factors: row.factors,
              type,
              confidence: row.confidence,
            });
          }
        });

      const arrFactors: IFactorInfo[] = [];
      factorMap.forEach((v) => arrFactors.push(v));
      arrFactors.sort((a, b) => b.confidence - a.confidence);

      setCompound(
        // ✅ 异步回调内 setState，合规
        arrFactors.map((item) => ({
          value: item.factors,
          label: (
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              {item.factors}
              {item.type === "user" ? (
                <TeamOutlined />
              ) : item.type === "robot" ? (
                <RobotOutlined />
              ) : (
                <></>
              )}
            </div>
          ),
        }))
      );
    });
    return () => {
      cancelled = true;
    };
  }, [word]);

  return (
    <div
      className="dict_compound_div"
      style={{
        width: "100%",
        maxWidth: 560,
        marginLeft: "auto",
        marginRight: "auto",
      }}
    >
      <Select
        getPopupContainer={() =>
          document.getElementsByClassName("dict_compound_div")[0] as HTMLElement
        }
        value={currValue}
        style={{ width: "100%" }}
        onChange={onSelectChange}
        options={factors}
      />
      {meaningData && meaningData.length > 0 ? (
        <List
          size="small"
          dataSource={meaningData}
          renderItem={(item) => (
            <List.Item>
              <div>
                <Link
                  strong
                  onClick={() => {
                    if (item.word) {
                      onSearch?.(item.word, true);
                    }
                  }}
                >
                  {item.word}
                </Link>{" "}
                <Text type="secondary">{item.meaning}</Text>
              </div>
            </List.Item>
          )}
        />
      ) : undefined}
    </div>
  );
};

export default CompoundWidget;
