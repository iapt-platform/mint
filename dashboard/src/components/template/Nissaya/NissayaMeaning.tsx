import { useAppSelector } from "../../../hooks";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import { NissayaCardPop } from "../../general/NissayaCard";

interface IWidget {
  text?: string;
  code?: string;
}

const NissayaMeaningWidget = ({ text, code = "my" }: IWidget) => {
  const endings = useAppSelector(getEnding);
  if (typeof text === "undefined") {
    return <></>;
  }
  let head = text;
  let end: string[] = [];
  for (let loop = 0; loop < 3; loop++) {
    for (let index = 0; index < head.length; index++) {
      const ending = head.slice(index);
      if (endings?.includes(ending)) {
        end.unshift(head.slice(index));
        head = head.slice(0, index);
      }
    }
  }
  const eEnding = end.map((item, id) => {
    return <NissayaCardPop text={item} key={id} trigger={item} />;
  });
  return (
    <>
      <span>{head}</span>
      {eEnding}
    </>
  );
};

export default NissayaMeaningWidget;
