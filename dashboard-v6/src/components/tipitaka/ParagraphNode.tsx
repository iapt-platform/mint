import { useState } from "react";
import { fetchParaNodeChunk, type IParagraphNode } from "../../api/pali-text";
import Paragraph from "./components/Paragraph";

interface IWidget {
  initData: IParagraphNode;
}
const ParagraphNode = ({ initData }: IWidget) => {
  const [data, setData] = useState<IParagraphNode>();
  const [loading, setLoading] = useState(false);
  const currData = data ?? initData;
  return (
    <>
      {currData && (
        <Paragraph
          loading={loading}
          {...currData}
          onModeChange={async (mode) => {
            setLoading(true);
            const newData = await fetchParaNodeChunk(
              initData.book,
              initData.para,
              initData.para,
              mode,
              initData.channels?.join(",")
            );
            setLoading(false);
            if (newData.ok && newData.data.items.length > 0) {
              setData(newData.data.items[0]);
            }
          }}
        />
      )}
    </>
  );
};

export default ParagraphNode;
