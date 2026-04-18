import { Popover, Tag } from "antd";

import NissayaItem from "./NissayaItem";
import Marked from "../general/Marked";

export interface INissaya {
  original?: string;
  translation?: string;
  note?: string;
  confidence?: number;
}
interface IWidget {
  data?: INissaya[];
}
const NissayaSent = ({ data }: IWidget) => {
  if (!data) {
    return <></>;
  }

  return (
    <>
      {data.map((item, id) => {
        return (
          <span key={id}>
            <NissayaItem
              pali={item.original}
              meaning={item.translation?.split(">")}
            />
            <>
              {item.confidence && item.confidence < 90 ? (
                <Tag color="red">{item.confidence}</Tag>
              ) : undefined}
            </>
            <>
              {item.note && (
                <Popover
                  styles={{ container: { width: 600 } }}
                  placement="bottom"
                  content={<Marked text={item.note} />}
                >
                  [nt]
                </Popover>
              )}
            </>
          </span>
        );
      })}
    </>
  );
};
export default NissayaSent;
