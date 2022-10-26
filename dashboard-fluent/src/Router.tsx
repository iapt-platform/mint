import { Route, Routes } from "react-router-dom";

import Home from "./pages/Home";
import NotFound from "./pages/NotFound";

const Widget = () => {
  return (
    <Routes>
      {/* PLEASE KEEP THOSE ARE THE LAST TWO ROUTES */}
      <Route path="" element={<Home />} />
      <Route path="*" element={<NotFound />} />
    </Routes>
  );
};

export default Widget;
