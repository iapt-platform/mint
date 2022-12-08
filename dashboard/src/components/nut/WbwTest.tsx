import { IWbw } from "../template/Wbw/WbwWord";
import WbwSent from "../template/WbwSent";

const Widget = () => {
  let wbwData: IWbw[] = [];
  const valueMake = (value: string) => {
    return { value: value, status: 3 };
  };
  const valueMake2 = (value: string[]) => {
    return { value: value, status: 3 };
  };
  for (let index = 0; index < 20; index++) {
    wbwData.push({
      word: valueMake("Word" + index),
      real: valueMake("word" + index),
      meaning: { value: ["意思" + index], status: 3 },
      factors: valueMake("word+word"),
      factorMeaning: valueMake("mean+mean"),
      type: valueMake(".n."),
      grammar: valueMake(".m.$.sg.$.nom."),
      case: valueMake2(["n", "m", "sg", "nom"]),
      confidence: 1,
    });
  }

  return (
    <div>
      <div style={{ width: 700 }}>
        <WbwSent
          data={wbwData}
          display="block"
          fields={{
            meaning: true,
            factors: true,
            factorMeaning: true,
            case: true,
          }}
        />
      </div>
    </div>
  );
};

export default Widget;
