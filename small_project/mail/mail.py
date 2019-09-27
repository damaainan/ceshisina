#coding=utf-8
import smtplib
from email.mime.text import MIMEText

fr = "master@example.com.cn"
fr = "junyuan@example.com.cn"
fr = "jiajunyuan@sharklasers.com"
# to = "damaainan@163.com"
to = "junyuan802@163.com"
cl = smtplib.SMTP(host='163mx02.mxmail.netease.com',port=25)
# cl = smtplib.SMTP(host='220.181.14.163',port=25)
cl.set_debuglevel(1)
cl.docmd("HELO server")
cl.docmd("MAIL FROM:<%s>"% fr)
cl.docmd("RCPT TO:<%s>"% to)

mess = "你算过命吗？我们不合适？你们合适？算出来有这些劫难了吗？"

msg = MIMEText(mess, _charset="utf-8")
msg['From'] = fr
msg['To'] = to
msg['Subject'] = "贾俊园"


cl.docmd("DATA")
cl.send(msg.as_string())
cl.send(".\n")
cl.quit()