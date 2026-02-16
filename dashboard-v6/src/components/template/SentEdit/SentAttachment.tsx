import { useEffect, useState } from "react";
import {
  IResAttachmentData,
  IResAttachmentListResponse,
} from "../../api/Attachments";
import { get } from "../../../request";

interface IWidget {
  sentenceId?: string;
}
const SentAttachment = ({ sentenceId }: IWidget) => {
  const [Attachments, setAttachments] = useState<IResAttachmentData[]>();
  useEffect(() => {
    if (!sentenceId) {
      return;
    }
    const url = `/v2/sentence-attachment?view=sentence&id=${sentenceId}`;
    console.debug("api request", url);
    get<IResAttachmentListResponse>(url).then((json) => {
      console.debug("api response", json);
      if (json.ok) {
        setAttachments(json.data.rows);
      }
    });
  }, [sentenceId]);
  return (
    <>
      {Attachments?.map((item, id) => {
        return <img key={id} src={item.attachment.url} alt="img" />;
      })}
    </>
  );
};

export default SentAttachment;
