import React, { useState } from "react";
import { Splitter } from "antd";
import { Outlet } from "react-router";
import RightPanel from "../../components/right-panel/RightPanel";

const defaultSizes = ["70%", "30%"];

const App: React.FC = () => {
  const [sizes, setSizes] = useState<(number | string)[]>(defaultSizes);

  const handleDoubleClick = () => {
    setSizes(defaultSizes);
  };

  return (
    <Splitter
      style={{ height: "100vh" }}
      onResize={setSizes}
      onDraggerDoubleClick={handleDoubleClick}
    >
      <Splitter.Panel size={sizes[0]}>
        <Outlet />
      </Splitter.Panel>

      <Splitter.Panel size={sizes[1]}>
        <RightPanel />
      </Splitter.Panel>
    </Splitter>
  );
};

export default App;
