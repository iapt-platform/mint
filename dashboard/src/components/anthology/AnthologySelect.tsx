import { Select } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IAnthologyListResponse } from "../api/Article";

interface IOptions {
  value: string;
  label: string;
}
interface IWidget {
  studioName?: string;
  onSelect?: Function;
}
const AnthologyTocTreeWidget = ({ studioName, onSelect }: IWidget) => {
  const [anthology, setAnthology] = useState<IOptions[]>([
    { value: "all", label: "全部" },
    { value: "none", label: "没有加入文集的" },
  ]);
  useEffect(() => {
    let url = `/v2/anthology?view=studio&name=${studioName}`;
    get<IAnthologyListResponse>(url).then((json) => {
      if (json.ok) {
        const data = json.data.rows.map((item) => {
          return {
            value: item.uid,
            label: item.title,
          };
        });
        setAnthology([
          { value: "all", label: "全部" },
          { value: "none", label: "没有加入文集的" },
          ...data,
        ]);
      }
    });
  }, [studioName]);
  return (
    <Select
      defaultValue="all"
      style={{ width: 180 }}
      onChange={(value: string) => {
        console.log(`selected ${value}`);
        if (typeof onSelect !== "undefined") {
          onSelect(value);
        }
      }}
      options={anthology}
    />
  );
};

export default AnthologyTocTreeWidget;
