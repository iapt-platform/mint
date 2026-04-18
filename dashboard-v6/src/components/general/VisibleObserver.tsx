import { useEffect, useRef, useState, type RefObject } from "react";

const useOnScreen = (ref: RefObject<HTMLElement | null>) => {
  const [isIntersecting, setIntersecting] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(([entry]) =>
      setIntersecting(entry.isIntersecting)
    );

    if (ref.current) {
      observer.observe(ref.current);
    }

    return () => {
      observer.disconnect();
    };
  }, [ref]);

  return isIntersecting;
};

interface IWidget {
  onVisible?: (isVisible: boolean) => void;
}

const VisibleObserverWidget = ({ onVisible }: IWidget) => {
  const ref = useRef<HTMLDivElement>(null);
  const isVisible = useOnScreen(ref);

  useEffect(() => {
    if (typeof onVisible !== "undefined") {
      onVisible(isVisible);
    }
  }, [isVisible, onVisible]);

  return (
    <div ref={ref} style={{ height: 20 }}>
      {" "}
    </div>
  );
};

export default VisibleObserverWidget;
