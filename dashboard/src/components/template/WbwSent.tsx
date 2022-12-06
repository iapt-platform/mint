import WbwWord, { IWbw, IWbwFields } from "./Wbw/WbwWord";

interface IWidget {
  data: IWbw[];
  display?: "block" | "inline";
  fields?: IWbwFields;
}
const Widget = ({ data, display, fields }: IWidget) => {
  const wbwSent = data.map((item, id) => {
    return (
      <WbwWord
        data={item}
        key={id}
        display={display}
        fields={fields}
        onChange={(e: IWbw) => {
          console.log("word changed", e);
          console.log("word id", id);
          //TODO update
        }}
      />
    );
  });
  return <div style={{ display: "flex", flexWrap: "wrap" }}>{wbwSent}</div>;
};

export default Widget;
