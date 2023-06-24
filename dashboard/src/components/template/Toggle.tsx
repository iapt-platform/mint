import { Tree } from "antd";
import { Children } from "react";

interface IToggleCtlWidget {
  children?: React.ReactNode | React.ReactNode[];
}
const ToggleCtl = ({ children }: IToggleCtlWidget) => {
  const arrayChildren = Children.toArray(children);
  if (arrayChildren.length === 0) {
    return <></>;
  } else {
    return (
      <Tree
        treeData={[
          {
            title: arrayChildren[0],
            key: "root",
            children: Children.map(arrayChildren, (child, index) => {
              if (index === 0) {
                return undefined;
              } else {
                return {
                  title: child as React.ReactElement,
                  key: index,
                };
              }
            }),
          },
        ]}
      />
    );
  }
};

interface IWidget {
  props?: string;
  children?: React.ReactNode | React.ReactNode[];
}
const ToggleWidget = ({ props, children }: IWidget) => {
  return <ToggleCtl>{children}</ToggleCtl>;
};

export default ToggleWidget;
