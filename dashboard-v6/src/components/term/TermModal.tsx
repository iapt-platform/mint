import type { ITermDataResponse } from "../../api/Term";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  id?: string;
  word?: string;
  tags?: string[];
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  community?: boolean;
  onUpdate?: (value: ITermDataResponse) => void;
  onClose?: () => void;
}
export const TermModalMock = ({ open, trigger }: IWidget) => {
  console.debug("TermModalMock", open);

  return (
    <>
      <span>{trigger}</span>
    </>
  );
};
