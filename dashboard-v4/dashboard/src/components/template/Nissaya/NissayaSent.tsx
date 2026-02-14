import { Popover, Tag } from "antd";
import { NissayaCtl } from "../Nissaya";
import Marked from "../../general/Marked";

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
          <>
            <NissayaCtl
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
                  overlayInnerStyle={{ width: 600 }}
                  placement="bottom"
                  content={<Marked text={item.note} />}
                >
                  [nt]
                </Popover>
              )}
            </>
          </>
        );
      })}
    </>
  );
};
export default NissayaSent;
