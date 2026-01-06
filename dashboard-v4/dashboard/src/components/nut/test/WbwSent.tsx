import { IWbw } from "../../template/Wbw/WbwWord";
import { WbwSentCtl } from "../../template/WbwSent";

const mockWbwData: IWbw[] = [
  {
    book: 1,
    para: 1,
    sn: [1],
    word: { value: "dhamma", status: 0 },
    real: { value: "dhamma", status: 0 },
    note: { value: "note", status: 0 },
    confidence: 0.95,
  },
  {
    book: 1,
    para: 1,
    sn: [2],
    word: { value: "Buddha", status: 0 },
    real: { value: "Buddha", status: 0 },
    confidence: 0.98,
  },
];
const WbwSent = () => {
  console.debug("test wbw render");
  return (
    <WbwSentCtl
      key={1}
      mode="edit"
      data={mockWbwData}
      book={1}
      para={2}
      wordEnd={10}
      wordStart={2}
      channelId="123"
    />
  );
};

export default WbwSent;
