import ChapterNew from "../../components/home/ChapterNew";
import CourseNew from "../../components/home/CourseNew";
import FooterBar from "../../components/library/FooterBar";
import HeadBar from "../../components/library/HeadBar";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <ChapterNew />
      <CourseNew />
      <FooterBar />
    </div>
  );
};

export default Widget;
