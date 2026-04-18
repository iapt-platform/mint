// features/dict/DictSearchWidget.tsx
// 胶水层：把 useDict 的数据喂给 DictContent，注入业务回调
// 不含纯样式逻辑，不直接发请求

import { Result, Skeleton } from "antd";
import { useDict } from "./hooks/useDict";
import DictContent from "./DictContent";
import type { ResultStatusType } from "antd/es/result";

interface IDictSearchWidgetProps {
  word: string | undefined;
  compact?: boolean;
}

const DictSearchWidget = ({
  word,
  compact = false,
}: IDictSearchWidgetProps) => {
  const { data, loading, errorCode, errorMessage } = useDict(word);

  if (loading) {
    return (
      <div>
        <div>searching {word}</div>
        <Skeleton active />
      </div>
    );
  }

  if (errorCode !== null) {
    return (
      <div>
        <Result
          status={errorCode as ResultStatusType}
          subTitle={errorMessage}
        />
      </div>
    );
  }

  return <DictContent word={word} data={data} compact={compact} />;
};

export default DictSearchWidget;
