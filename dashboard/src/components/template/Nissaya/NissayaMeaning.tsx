import GrammarPop from "../../dict/GrammarPop";

interface IWidget {
  text?: string;
  code?: string;
}

interface IEnding {
  text: string;
  gid: string;
}
//缅文语尾高亮和提示气泡
function myEndingTooltip(inStr?: string): JSX.Element {
  if (typeof inStr === "undefined") {
    return <></>;
  }
  let myEnding = [
    {
      id: "my_nom1",
      name: "သည်",
      tooltip: "主语",
    },
    {
      id: "my_nom2",
      name: "ကား",
      tooltip: "主格/主语",
    },
    {
      id: "my_nom3",
      name: "က",
      tooltip: "主格/主语",
    },
    {
      id: "my_acc1",
      name: "ကို",
      tooltip: "宾格/宾语",
    },
    {
      id: "my_acc2",
      name: "သို့",
      tooltip: "宾格/趋向",
    },
    {
      id: "my_inst1",
      name: "ဖြင့်",
      tooltip: "具格/用",
    },
    {
      id: "my_inst2",
      name: "နှင့်",
      tooltip: "具格/与",
    },
    {
      id: "my_inst2",
      name: "နှင့်",
      tooltip: "具格/与",
    },
    {
      id: "my_inst3",
      name: "ကြောင့်",
      tooltip: "具格/凭借;从格/原因",
    },
    {
      id: "my_inst3",
      name: "ကြောင်း",
      tooltip: "具格/凭借;从格/原因",
    },
    {
      id: "my_dat1",
      name: "အား",
      tooltip: "目的格/对象(间接宾语)，对……来说",
    },
    {
      id: "my_dat2",
      name: "ငှာ",
      tooltip: "目的格/表示目的，为了……",
    },
    {
      id: "my_dat2",
      name: "အတွက်",
      tooltip: "目的格/表示目的，为了……",
    },
    {
      id: "my_abl1",
      name: "မှ",
      tooltip: "从格/表示来源，从……",
    },
    {
      id: "my_abl2",
      name: "အောက်",
      tooltip: "从格/表达比较，比……多",
    },
    {
      id: "my_abl3",
      name: "ထက်",
      tooltip: "从格/表达比较，比……少",
    },
    {
      id: "my_gen1",
      name: "၏",
      tooltip: "属格/的",
    },
    {
      id: "my_gen2",
      name: "တွင်",
      tooltip: "属格/表达范围，……中的",
    },
    {
      id: "my_loc1",
      name: "၌",
      tooltip: "处格/处(范围)",
    },
    {
      id: "my_loc2",
      name: "ကြောင့်",
      tooltip: "处格/表达动机，因……，旨在……",
    },
    {
      id: "my_abs",
      name: "၍",
      tooltip: "连续体",
    },
    {
      id: "my_pl",
      name: "တို့",
      tooltip: "复数",
    },
    {
      id: "my_pl",
      name: "များ",
      tooltip: "复数",
    },
    {
      id: "my_pl",
      name: "ကုန်",
      tooltip: "复数",
    },
    {
      id: "my_pl",
      name: "ကြ",
      tooltip: "复数",
    },
    {
      id: "my_time",
      name: "ပတ်လုံး",
      tooltip: "时间的整数",
    },
    {
      id: "my_time",
      name: "လုံလုံး",
      tooltip: "时间的整数",
    },
    {
      id: "my_length",
      name: "တိုင်တိုင်",
      tooltip: "距离,长度的整数",
    },
    {
      id: "my_length",
      name: "တိုင်အောင်",
      tooltip: "距离,长度的整数",
    },
    {
      id: "my_def",
      name: "နေစဉ်",
      tooltip: "同时发生的时间状语(当……的时候)",
    },
    {
      id: "my_def",
      name: "လျက်",
      tooltip: "同时发生的时间状语(当……的时候)",
    },
  ];

  let ending: IEnding[] = [];
  let head: string = inStr;
  for (const iterator of myEnding) {
    if (inStr.indexOf(iterator.name) === inStr.length - iterator.name.length) {
      head = inStr.substring(0, inStr.indexOf(iterator.name));
      ending.push({ text: iterator.name, gid: iterator.id });
    }
  }
  const eEnding = ending.map((item, id) => {
    return <GrammarPop text={item.text} key={id} gid={`grammar_${item.gid}`} />;
  });

  return (
    <>
      <span>{head}</span>
      {eEnding}
    </>
  );
}
const Widget = ({ text, code = "my" }: IWidget) => {
  console.log("nissaya-meaning", text);
  return myEndingTooltip(text);
};

export default Widget;
