import { IFinal } from "../api/Channel";

interface IWidget {
  data?: IFinal[];
  width?: number;
}
const ProgressSvgWidget = ({ data, width = 300 }: IWidget) => {
  //绘制句子进度
  if (typeof data === "undefined" || data.length === 0) {
    return <></>;
  }
  //进度
  let svg_width = 0;
  if (data) {
    for (const iterator of data) {
      svg_width += iterator[0];
    }
  }

  const svg_height = svg_width / 10;

  let curr_x = 0;
  let finished = 0;

  const innerBar = data?.map((item, id) => {
    const stroke_width = item[0];
    curr_x += stroke_width;
    finished += item[1] ? stroke_width : 0;
    return (
      <rect
        key={id}
        x={curr_x - stroke_width}
        y={0}
        height={svg_height}
        width={stroke_width}
        fill={item[1] ? "url(#grad1)" : "url(#grad2)"}
      />
    );
  });
  const finishedBar = (
    <rect
      key="2"
      x={0}
      y={svg_height / 2 - svg_height / 20}
      width={finished}
      height={svg_height / 10}
      style={{ strokeWidth: 0, fill: "rgb(100, 100, 228)" }}
    />
  );
  const progress = (
    <svg viewBox={`0 0 ${svg_width} ${svg_height} `} width={"100%"}>
      <defs>
        <linearGradient key="1" id="grad1" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop
            key="1"
            offset="0%"
            style={{ stopColor: "rgb(0,180,0)", stopOpacity: 1 }}
          />
          <stop
            key="2"
            offset="50%"
            style={{ stopColor: "rgb(255,255,255)", stopOpacity: 0.5 }}
          />
          <stop
            key="3"
            offset="100%"
            style={{ stopColor: "rgb(0,180,0)", stopOpacity: 1 }}
          />
        </linearGradient>
        <linearGradient key="2" id="grad2" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop
            key="1"
            offset="0%"
            style={{ stopColor: "rgb(180,180,180)", stopOpacity: 1 }}
          />
          <stop
            key="2"
            offset="50%"
            style={{ stopColor: "rgb(255,255,255)", stopOpacity: 0.5 }}
          />
          <stop
            key="3"
            offset="100%"
            style={{ stopColor: "rgb(180,180,180)", stopOpacity: 1 }}
          />
        </linearGradient>
      </defs>
      {innerBar}
      {finishedBar}
    </svg>
  );
  return <div style={{ width: width }}>{progress}</div>;
};

export default ProgressSvgWidget;
