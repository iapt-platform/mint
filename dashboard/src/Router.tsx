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
import LibraryDictRecent from "./pages/library/dict/recent";
import LibraryAnthology from "./pages/library/anthology";
import LibraryAnthologyShow from "./pages/library/anthology/show";
import LibraryAnthologyList from "./pages/library/anthology/list";
import LibraryArticle from "./pages/library/anthology/article";

import LibraryBlog from "./pages/library/blog";
import LibraryBlogOverview from "./pages/library/blog/overview";
import LibraryBlogTranslation from "./pages/library/blog/translation";
import LibraryBlogCourse from "./pages/library/blog/course";
import LibraryBlogAnthology from "./pages/library/blog/anthology";
import LibraryBlogTerm from "./pages/library/blog/term";

import StudioHome from "./pages/studio";

import StudioPalicanon from "./pages/studio/palicanon";
import StudioRecent from "./pages/studio/recent";

import StudioChannel from "./pages/studio/channel";
import StudioChannelEdit from "./pages/studio/channel/edit";

import StudioGroup from "./pages/studio/group";
import StudioGroupEdit from "./pages/studio/group/edit";
import StudioGroupShow from "./pages/studio/group/show";

import StudioDict from "./pages/studio/dict";
import StudioTerm from "./pages/studio/term";

import StudioArticle from "./pages/studio/article";
import StudioArticleEdit from "./pages/studio/article/edit";

import StudioAnthology from "./pages/studio/anthology";
import StudioAnthologyEdit from "./pages/studio/anthology/edit";

import StudioAnalysis from "./pages/studio/analysis";

const Widget = () => {
	return (
		<Routes>
			<Route path="anonymous" element={<Anonymous />}>
				<Route path="users">
					<Route path="sign-in" element={<NutUsersSignIn />} />
					<Route path="sign-up" element={<NutUsersSignUp />} />

					<Route path="unlock">
						<Route path="new" element={<NutUsersUnlockNew />} />
						<Route path="verify/:token" element={<NutUsersUnlockVerify />} />
					</Route>
					<Route path="reset-password/:token" element={<NutUsersResetPassword />} />
					<Route path="forgot-password" element={<NutUsersForgotPassword />} />
				</Route>
			</Route>

			<Route path="dashboard" element={<Dashboard />}>
				<Route path="users">
					<Route path="change-password" element={<NutUsersChangePassword />} />
					<Route path="logs" element={<NutUsersLogs />} />
					<Route path="account-info" element={<NutUsersAccountInfo />} />
				</Route>
			</Route>
			<Route path="switch-language" element={<NutSwitchLanguage />} />
			<Route path="forbidden" element={<NutForbidden />} />
			<Route path="" element={<NutHome />} />
			<Route path="*" element={<NutNotFound />} />

			<Route path="community" element={<LibraryCommunity />}>
				<Route path="list" element={<LibraryCommunityList />} />
				<Route path="recent" element={<LibraryCommunityRecent />} />
			</Route>

			<Route path="palicanon" element={<LibraryPalicanon />}>
				<Route path="list" element={<LibraryPalicanonByPath />} />
				<Route path="list/:root" element={<LibraryPalicanonByPath />} />
				<Route path="list/:root/:path" element={<LibraryPalicanonByPath />} />
				<Route path="list/:root/:path/:tag" element={<LibraryPalicanonByPath />} />
				<Route path="chapter/:id" element={<LibraryPalicanonChapter />} />
			</Route>

			<Route path="course" element={<LibraryCourse />}>
				<Route path="show/:id" element={<LibraryCourseShow />}></Route>
				<Route path="lesson/:id" element={<LibraryLessonShow />}></Route>
				<Route path="course/:id" element={<LibraryLessonShow />}></Route>
				<Route path="list" element={<LibraryCourseList />}></Route>
			</Route>

			<Route path="term/:word" element={<LibraryTerm />} />

			<Route path="dict" element={<LibraryDict />}>
				<Route path=":word" element={<LibraryDictShow />} />
				<Route path="recent" element={<LibraryDictRecent />} />
			</Route>

			<Route path="anthology" element={<LibraryAnthology />}>
				<Route path="list" element={<LibraryAnthologyList />} />
				<Route path=":id" element={<LibraryAnthologyShow />} />
				<Route path=":id/by_channel/:tags" element={<LibraryAnthologyShow />} />
			</Route>
			<Route path="article/show/:id" element={<LibraryArticle />} />

			<Route path="blog/:studio" element={<LibraryBlog />}>
				<Route path="overview" element={<LibraryBlogOverview />} />
				<Route path="palicanon" element={<LibraryBlogTranslation />} />
				<Route path="course" element={<LibraryBlogCourse />} />
				<Route path="anthology" element={<LibraryBlogAnthology />} />
				<Route path="term" element={<LibraryBlogTerm />} />
			</Route>

			<Route path="studio/:studioname" element={<StudioHome />}></Route>
			<Route path="studio/:studioname/palicanon" element={<StudioPalicanon />}></Route>
			<Route path="studio/:studioname/recent" element={<StudioRecent />}></Route>

			<Route path="studio/:studioname/channel" element={<StudioChannel />}></Route>
			<Route path="studio/:studioname/channel/edit/:channelid" element={<StudioChannelEdit />} />

			<Route path="studio/:studioname/group" element={<StudioGroup />}></Route>
			<Route path="studio/:studioname/group/:groupid" element={<StudioGroupShow />} />
			<Route path="studio/:studioname/group/edit/:groupid" element={<StudioGroupEdit />} />

			<Route path="studio/:studioname/dict" element={<StudioDict />} />
			<Route path="studio/:studioname/term" element={<StudioTerm />} />

			<Route path="studio/:studioname/article" element={<StudioArticle />}></Route>
			<Route path="studio/:studioname/article/edit/:articleid" element={<StudioArticleEdit />} />

			<Route path="studio/:studioname/anthology" element={<StudioAnthology />}></Route>
			<Route path="studio/:studioname/anthology/edit/:anthology_id" element={<StudioAnthologyEdit />} />

			<Route path="studio/:studioname/analysis" element={<StudioAnalysis />}></Route>
		</Routes>
	);
};

export default Widget;
