import { type Request, type Response } from "express";

export const heartbeat = (req: Request, res: Response) => {
  res.status(200).json({ createdAt: new Date() });
};
