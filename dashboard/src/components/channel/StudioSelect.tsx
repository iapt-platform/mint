import { Select } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { IStudio } from "../auth/Studio";

interface IStudioListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IStudio[];
    count: number;
  };
}

interface IOptions {
  value: string;
  label?: string;
}
interface IWidget {
  studioName?: string;
  onSelect?: Function;
}
const StudioSelectWidget = ({ studioName, onSelect }: IWidget) => {
  const [anthology, setAnthology] = useState<IOptions[]>([
    { value: "all", label: "全部" },
  ]);
  useEffect(() => {
    let url = `/v2/studio?view=collaboration-channel&studio_name=${studioName}`;
    get<IStudioListResponse>(url).then((json) => {
      if (json.ok) {
        const data = json.data.rows
          .sort((a, b) => {
            if (a.nickName && b.nickName && a.nickName < b.nickName) {
              return -1;
            } else {
              return 1;
            }
          })
          .map((item) => {
            return {
              value: item.id,
              label: item.nickName,
            };
          });
        setAnthology([{ value: "all", label: "全部" }, ...data]);
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

export default StudioSelectWidget;
