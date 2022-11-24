import Icon from "@ant-design/icons";
import type { CustomIconComponentProps } from "@ant-design/icons/lib/components/Icon";

const DictSvg = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="1em"
    height="1em"
    fill="currentColor"
    viewBox="0 0 32 32"
  >
    <g transform="translate(-4 -4)">
      <path
        d="M24.4,2,17.9,7.85v14.3l6.5-5.85V2M8.15,5.9A12.09,12.09,0,0,0,1,7.85V26.908a.7.7,0,0,0,.65.65c.13,0,.195-.091.325-.091A15.85,15.85,0,0,1,8.15,26.05,12.09,12.09,0,0,1,15.3,28a15.659,15.659,0,0,1,7.15-1.95,13.241,13.241,0,0,1,6.175,1.378.565.565,0,0,0,.325.039.7.7,0,0,0,.65-.65V7.85A8.867,8.867,0,0,0,27,6.55V24.1a15.106,15.106,0,0,0-4.55-.65A15.659,15.659,0,0,0,15.3,25.4V7.85A12.09,12.09,0,0,0,8.15,5.9Z"
        transform="translate(5 4)"
      />
    </g>
  </svg>
);

const TermSvg = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="1em"
    height="1em"
    fill="currentColor"
    viewBox="0 0 24 24"
  >
    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z" />
  </svg>
);

export const DictIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={DictSvg} {...props} />
);
export const TermIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={TermSvg} {...props} />
);
