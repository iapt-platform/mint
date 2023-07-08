import { useEffect, useRef, useState } from "react";

const useOnScreen = (ref: any) => {
  const [isIntersecting, setIntersecting] = useState(false);
  const observer = new IntersectionObserver(([entry]) =>
    setIntersecting(entry.isIntersecting)
  );
  useEffect(() => {
    observer.observe(ref.current);
    return () => {
      observer.disconnect();
    };
  }, []);

  return isIntersecting;
};
interface IWidget {
  onVisible?: Function;
}
const VisibleObserverWidget = ({ onVisible }: IWidget) => {
  const ref = useRef<HTMLDivElement>(null);
  const isVisible = useOnScreen(ref);

  useEffect(() => {
    if (typeof onVisible !== "undefined") {
      onVisible(isVisible);
    }
  }, [isVisible]);
  return (
    <div ref={ref} style={{ height: 20 }}>
      {" "}
    </div>
  );
};

export default VisibleObserverWidget;
