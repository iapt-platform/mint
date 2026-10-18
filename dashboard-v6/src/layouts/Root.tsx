import { Outlet } from "react-router";

import DiscussionBadgeLoader from "../components/discussion/DiscussionBadgeLoader";
import TagsLoader from "../components/tag/TagsLoader";

const Widget = () => {
  // TODO

  return (
    <div>
      {/* 全站各一份：讨论角标（v3）与句子标签（仍走 v2），攒批取数 */}
      <DiscussionBadgeLoader />
      <TagsLoader />
      <Outlet />
    </div>
  );
};

export default Widget;
