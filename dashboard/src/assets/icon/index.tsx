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

const SuggestionSvg = () => (
  <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 235 235">
    <path d="M 159.635,82.894 C 148.467,72.784 133.286,65 117,65 100.715,65 85.656,72.783 74.487,82.894 62.844,93.434 56.431,106.871 56.431,123 c 0,27.668 15.389,43.912 20.445,48.839 1.361,1.326 4.104,4.766 7.428,9.145 -2.43,2.298 -3.934,5.412 -3.934,8.844 0,4.016 2.053,7.599 5.249,9.943 -0.298,0.974 -0.47,1.995 -0.47,3.055 0,3.836 2.096,7.228 5.292,9.293 -0.499,1.149 -0.781,2.602 -0.781,3.911 0,5.556 4.935,8.97 11,8.97 h 32.802 c 6.064,0 10.999,-3.414 10.999,-8.97 0,-1.309 -0.283,-2.66 -0.781,-3.808 3.196,-2.064 5.293,-5.508 5.293,-9.344 0,-1.06 -0.172,-2.107 -0.47,-3.081 3.196,-2.344 5.248,-5.94 5.248,-9.956 0,-3.36 -1.445,-6.419 -3.786,-8.702 3.46,-4.511 6.319,-8.068 7.721,-9.43 13.461,-13.067 20.005,-29.843 20.005,-48.709 0,-16.129 -6.413,-29.566 -18.056,-40.106 z M 117,80 l 43,55 h -29.273 v 75 h -28 V 135 H 74 Z" />
    <path d="m 117,60.8955 c 6.56,0 16,-5.317 16,-11.877 V 19.688 c 0,-6.56 -9.44,-11.877 -16,-11.877 -6.56,0 -16,5.317 -16,11.877 v 29.3315 c 0,6.559 9.44,11.876 16,11.876 z" />
    <path d="m 222.244,106 h -29.3305 c -6.56,0 -11.877,10.44 -11.877,17 0,6.56 5.317,17 11.877,17 h 29.3305 c 6.56,0 11.877,-10.44 11.877,-17 0,-6.56 -5.317,-17 -11.877,-17 z" />
    <path d="M 41.2085,106 H 11.877 C 5.317,106 0,116.44 0,123 c 0,6.56 5.317,17 11.877,17 h 29.3315 c 6.56,0 11.877,-10.44 11.877,-17 0,-6.56 -5.317,-17 -11.877,-17 z" />
    <path d="M 72.31325,55.08925 49.63875,33.98275 C 44.76375,29.59475 34.2525,32.991 29.8655,37.866 c -4.388,4.876 -6.99275,15.38625 -2.11675,19.77325 l 22.6745,21.1055 c 2.27,2.043 5.84469,1.464413 8.67569,1.464413 3.25,0 8.75206,-2.741663 11.09706,-5.347663 4.387,-4.875 6.99225,-15.38425 2.11725,-19.77225 z" />
    <path d="m 204.2555,37.8645 c -4.39,-4.877 -13.898,-8.27125 -18.773,-3.88325 l -22.673,21.1075 c -4.876,4.389 -3.271,14.89825 1.117,19.77325 2.346,2.606 5.582,5.347663 8.832,5.347663 2.831,0 8.672,0.578587 10.941,-1.464413 l 22.673,-21.1065 c 4.876,-4.389 2.271,-14.89925 -2.117,-19.77425 z" />
  </svg>
);

const LockSvg = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="1em"
    height="1em"
    fill="currentColor"
    viewBox="0 0 16 16"
  >
    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z" />
  </svg>
);

const UnLockSvg = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="1em"
    height="1em"
    fill="currentColor"
    viewBox="0 0 16 16"
  >
    <path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2zM3 8a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H3z" />
  </svg>
);

const HandOutlined = () => (
  <svg
    viewBox="0 0 1024 1024"
    version="1.1"
    xmlns="http://www.w3.org/2000/svg"
    p-id="1704"
    width="1em"
    height="1em"
    fill="currentColor"
  >
    <path
      d="M870.4 204.8c-18.6368 0-36.1472 5.0176-51.2 13.7728l0-64.9728c0-56.4736-45.9264-102.4-102.4-102.4-21.0944 0-40.6528 6.4-56.9856 17.3568-14.0288-39.8848-52.0192-68.5568-96.6144-68.5568s-82.6368 28.672-96.6144 68.5568c-16.2816-10.9568-35.8912-17.3568-56.9856-17.3568-56.4736 0-102.4 45.9264-102.4 102.4l0 377.4976-68.9152-119.4496c-13.3632-24.32-35.1744-41.6256-61.3888-48.7936-25.5488-6.9632-52.1216-3.2768-74.8544 10.3424-46.4384 27.8528-64.1536 90.8288-39.424 140.3904 1.536 3.1232 34.2016 70.0416 136.192 273.92 48.0256 96 100.7104 164.6592 156.6208 203.9808 43.8784 30.8736 74.1888 32.4608 79.8208 32.4608l256 0c43.5712 0 84.0704-14.1824 120.4224-42.0864 34.1504-26.2656 63.7952-64.256 88.064-112.8448 47.8208-95.6416 73.1136-227.9424 73.1136-382.6688l0-179.2c0-56.4736-45.9264-102.4-102.4-102.4zM921.6 486.4c0 146.7904-23.3984 271.1552-67.6864 359.7312-28.8768 57.7536-80.5888 126.6688-162.7136 126.6688l-255.488 0c-1.9968-0.1536-23.552-2.56-56.064-26.88-32.4096-24.2688-82.176-75.3664-135.0656-181.248-103.7824-207.5648-135.68-272.9472-135.9872-273.5616-0.0512-0.1024-0.0512-0.1536-0.1024-0.2048-12.8512-25.7536-3.7376-59.4944 19.9168-73.6768 10.6496-6.4 23.0912-8.0896 35.072-4.864 12.7488 3.4816 23.4496 12.0832 30.0544 24.1664 0.1024 0.1536 0.2048 0.3584 0.3072 0.512l79.9232 138.496c16.3328 29.8496 34.7136 42.3936 54.6304 37.3248 19.968-5.0688 30.0544-25.0368 30.0544-59.2384l0-400.0256c0-28.2112 22.9888-51.2 51.2-51.2s51.2 22.9888 51.2 51.2l0 332.8c0 14.1312 11.4688 25.6 25.6 25.6s25.6-11.4688 25.6-25.6l0-384c0-28.2112 22.9888-51.2 51.2-51.2s51.2 22.9888 51.2 51.2l0 384c0 14.1312 11.4688 25.6 25.6 25.6s25.6-11.4688 25.6-25.6l0-332.8c0-28.2112 22.9888-51.2 51.2-51.2s51.2 22.9888 51.2 51.2l0 384c0 14.1312 11.4688 25.6 25.6 25.6s25.6-11.4688 25.6-25.6l0-230.4c0-28.2112 22.9888-51.2 51.2-51.2s51.2 22.9888 51.2 51.2l0 179.2z"
      p-id="1705"
    ></path>
  </svg>
);
export const DictIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={DictSvg} {...props} />
);
export const TermIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={TermSvg} {...props} />
);

export const SuggestionIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={SuggestionSvg} {...props} />
);

export const LockIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={LockSvg} {...props} />
);

export const UnLockIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={UnLockSvg} {...props} />
);

export const HandOutlinedIcon = (props: Partial<CustomIconComponentProps>) => (
  <Icon component={HandOutlined} {...props} />
);
