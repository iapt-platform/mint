import { Route, Routes } from "react-router-dom";

import Anonymous from "./layouts/anonymous";
import Dashboard from "./layouts/dashboard";

import NutUsersSignIn from "./pages/nut/users/sign-in";
import NutUsersSignUp from "./pages/nut/users/sign-up";
import NutUsersUnlockNew from "./pages/nut/users/unlock/new";
import NutUsersUnlockVerify from "./pages/nut/users/unlock/verify";
import NutUsersResetPassword from "./pages/nut/users/reset-password";
import NutUsersForgotPassword from "./pages/nut/users/forgot-password";
import NutUsersChangePassword from "./pages/nut/users/change-password";
import NutUsersAccountInfo from "./pages/nut/users/account-info";
import NutUsersLogs from "./pages/nut/users/logs";
import NutForbidden from "./pages/nut/forbidden";
import NutNotFound from "./pages/nut/not-found";
import NutSwitchLanguage from "./pages/nut/switch-languages";
import NutHome from "./pages/nut";

import AdminHome from "./pages/admin";
import LibraryHome from "./pages/library";
import LibraryCommunity from "./pages/library/community";
import LibraryCommunityList from "./pages/library/community/list";
import LibraryCommunityRecent from "./pages/library/community/recent";
import LibraryPalicanon from "./pages/library/palicanon";
import LibraryPalicanonByPath from "./pages/library/palicanon/bypath";
import LibraryPalicanonChapter from "./pages/library/palicanon/chapter";
import LibraryCourse from "./pages/library/course";
import LibraryCourseList from "./pages/library/course/list";
import LibraryCourseShow from "./pages/library/course/course";
import LibraryLessonShow from "./pages/library/course/lesson";

import LibraryTerm from "./pages/library/term/show";
import LibraryDict from "./pages/library/dict";
import LibraryDictShow from "./pages/library/dict/show";
import LibraryAnthology from "./pages/library/anthology";
import LibraryAnthologyShow from "./pages/library/anthology/show";
import LibraryAnthologyList from "./pages/library/anthology/list";
import LibraryArticle from "./pages/library/article";
import LibraryArticleShow from "./pages/library/article/show";

import LibraryBlog from "./pages/library/blog";
import LibraryBlogOverview from "./pages/library/blog/overview";
import LibraryBlogTranslation from "./pages/library/blog/translation";
import LibraryBlogCourse from "./pages/library/blog/course";
import LibraryBlogAnthology from "./pages/library/blog/anthology";
import LibraryBlogTerm from "./pages/library/blog/term";

import LibraryDiscussion from "./pages/library/discussion";
import LibraryDiscussionList from "./pages/library/discussion/list";
import LibraryDiscussionTopic from "./pages/library/discussion/topic";

import Studio from "./pages/studio";
import StudioHome from "./pages/studio/home";

import StudioPalicanon from "./pages/studio/palicanon";
import StudioRecent from "./pages/studio/recent";

import StudioChannel from "./pages/studio/channel";
import StudioChannelList from "./pages/studio/channel/list";
import StudioChannelEdit from "./pages/studio/channel/edit";
import StudioChannelShow from "./pages/studio/channel/show";

import StudioGroup from "./pages/studio/group";
import StudioGroupList from "./pages/studio/group/list";
import StudioGroupEdit from "./pages/studio/group/edit";
import StudioGroupShow from "./pages/studio/group/show";

import StudioCourse from "./pages/studio/course";
import StudioCourseList from "./pages/studio/course/list";
import StudioCourseEdit from "./pages/studio/course/edit";

import StudioDict from "./pages/studio/dict";
import StudioDictList from "./pages/studio/dict/list";

import StudioTerm from "./pages/studio/term";
import StudioTermList from "./pages/studio/term/list";

import StudioArticle from "./pages/studio/article";
import StudioArticleList from "./pages/studio/article/list";
import StudioArticleEdit from "./pages/studio/article/edit";

import StudioAnthology from "./pages/studio/anthology";
import StudioAnthologyList from "./pages/studio/anthology/list";
import StudioAnthologyEdit from "./pages/studio/anthology/edit";

import StudioSetting from "./pages/studio/setting";

import StudioAnalysis from "./pages/studio/analysis";
import StudioAnalysisList from "./pages/studio/analysis/list";
import { ConfigProvider } from "antd";
import { useAppSelector } from "./hooks";
import { currTheme } from "./reducers/theme";

const Widget = () => {
  const theme = useAppSelector(currTheme);
  return (
    <ConfigProvider prefixCls={theme}>
      <Routes>
        <Route path="admin" element={<AdminHome />} />
        <Route path="anonymous" element={<Anonymous />}>
          <Route path="users">
            <Route path="sign-in" element={<NutUsersSignIn />} />
            <Route path="sign-up" element={<NutUsersSignUp />} />

            <Route path="unlock">
              <Route path="new" element={<NutUsersUnlockNew />} />
              <Route path="verify/:token" element={<NutUsersUnlockVerify />} />
            </Route>
            <Route
              path="reset-password/:token"
              element={<NutUsersResetPassword />}
            />
            <Route
              path="forgot-password"
              element={<NutUsersForgotPassword />}
            />
          </Route>
        </Route>

        <Route path="dashboard" element={<Dashboard />}>
          <Route path="users">
            <Route
              path="change-password"
              element={<NutUsersChangePassword />}
            />
            <Route path="logs" element={<NutUsersLogs />} />
            <Route path="account-info" element={<NutUsersAccountInfo />} />
          </Route>
        </Route>
        <Route path="switch-language" element={<NutSwitchLanguage />} />
        <Route path="forbidden" element={<NutForbidden />} />
        <Route path="nut" element={<NutHome />} />
        <Route path="" element={<LibraryHome />} />
        <Route path="*" element={<NutNotFound />} />

        <Route path="community" element={<LibraryCommunity />}>
          <Route path="list" element={<LibraryCommunityList />} />
          <Route path="recent" element={<LibraryCommunityRecent />} />
        </Route>

        <Route path="palicanon" element={<LibraryPalicanon />}>
          <Route path="list" element={<LibraryPalicanonByPath />} />
          <Route path="list/:root" element={<LibraryPalicanonByPath />} />
          <Route path="list/:root/:path" element={<LibraryPalicanonByPath />} />
          <Route
            path="list/:root/:path/:tag"
            element={<LibraryPalicanonByPath />}
          />
          <Route path="chapter/:id" element={<LibraryPalicanonChapter />} />
        </Route>

        <Route path="course" element={<LibraryCourse />}>
          <Route path="list" element={<LibraryCourseList />}></Route>
          <Route path="show/:id" element={<LibraryCourseShow />}></Route>
          <Route path="lesson" element={<LibraryLessonShow />}></Route>
        </Route>

        <Route path="term/:word" element={<LibraryTerm />} />

        <Route path="dict" element={<LibraryDict />}>
          <Route path=":word" element={<LibraryDictShow />} />
          <Route path="recent" element={<LibraryDictShow />} />
        </Route>

        <Route path="anthology" element={<LibraryAnthology />}>
          <Route path="list" element={<LibraryAnthologyList />} />
          <Route path=":id" element={<LibraryAnthologyShow />} />
          <Route
            path=":id/by_channel/:tags"
            element={<LibraryAnthologyShow />}
          />
        </Route>

        <Route path="article" element={<LibraryArticle />}>
          <Route path=":type/:id" element={<LibraryArticleShow />} />
          <Route path=":type/:id/:mode" element={<LibraryArticleShow />} />
          <Route
            path=":type/:id/:mode/:param"
            element={<LibraryArticleShow />}
          />
        </Route>

        <Route path="discussion" element={<LibraryDiscussion />}>
          <Route path="list" element={<LibraryDiscussionList />} />
          <Route path="topic/:id" element={<LibraryDiscussionTopic />} />
          <Route path="discussion/:id" element={<LibraryDiscussion />} />
        </Route>

        <Route path="blog/:studio" element={<LibraryBlog />}>
          <Route path="overview" element={<LibraryBlogOverview />} />
          <Route path="palicanon" element={<LibraryBlogTranslation />} />
          <Route path="course" element={<LibraryBlogCourse />} />
          <Route path="anthology" element={<LibraryBlogAnthology />} />
          <Route path="term" element={<LibraryBlogTerm />} />
        </Route>

        <Route path="studio/:studioname" element={<Studio />}>
          <Route path="home" element={<StudioHome />} />
          <Route path="palicanon" element={<StudioPalicanon />}></Route>
          <Route path="recent" element={<StudioRecent />}></Route>

          <Route path="channel" element={<StudioChannel />}>
            <Route path="list" element={<StudioChannelList />} />
            <Route path=":channelid/edit" element={<StudioChannelEdit />} />
            <Route path=":channelId" element={<StudioChannelShow />} />
          </Route>

          <Route path="group" element={<StudioGroup />}>
            <Route path="list" element={<StudioGroupList />} />
            <Route path=":groupId" element={<StudioGroupShow />} />
            <Route path=":groupId/edit" element={<StudioGroupEdit />} />
            <Route path=":groupId/show" element={<StudioGroupShow />} />
          </Route>

          <Route path="course" element={<StudioCourse />}>
            <Route path="list" element={<StudioCourseList />} />
            <Route path=":courseId/edit" element={<StudioCourseEdit />} />
          </Route>

          <Route path="dict" element={<StudioDict />}>
            <Route path="list" element={<StudioDictList />} />
          </Route>

          <Route path="term" element={<StudioTerm />}>
            <Route path="list" element={<StudioTermList />} />
          </Route>

          <Route path="article" element={<StudioArticle />}>
            <Route path="list" element={<StudioArticleList />} />
            <Route path=":articleid/edit" element={<StudioArticleEdit />} />
          </Route>

          <Route path="anthology" element={<StudioAnthology />}>
            <Route path="list" element={<StudioAnthologyList />}></Route>
            <Route
              path=":anthology_id/edit"
              element={<StudioAnthologyEdit />}
            />
          </Route>
          <Route path="setting" element={<StudioSetting />} />

          <Route path="exp" element={<StudioAnalysis />}>
            <Route path="list" element={<StudioAnalysisList />} />
          </Route>
        </Route>
      </Routes>
    </ConfigProvider>
  );
};

export default Widget;
