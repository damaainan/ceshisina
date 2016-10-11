#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import requests
from bs4 import BeautifulSoup
import sys
import os

link=sys.argv[1]
name=link.split("=")[-1]
os.mkdir(name)

# r=requests.get("http://www.kuwo.cn/artist/content?name=%E5%A7%9C%E6%98%95")
r=requests.get(link)
html=r.text
soup = BeautifulSoup(html,"html5lib")
# items=soup.find('ul',class_='listMusic')
# print(items)
artid=soup.find("div",class_="artistTop")['data-artistid']
# print(artid['data-artistid'])
# artid=artid['data-artistid']
page=soup.find("div",class_="page")
pagenum=int(page['data-page'])
# pagenum=int(pagenum)
# print(artid)
# print(pagenum)
# print(type(artid))
# print(type(pagenum))
i=0
urls=set([])
while i<pagenum:
	k=i
	i+=1
	url="http://www.kuwo.cn/artist/contentMusicsAjax?artistId="+artid+"&pn="+str(k)+"&rn=15"
	r2=requests.get(url)
	html2=r2.text
	soup2=BeautifulSoup(html2,"html5lib")
	items2=soup2.find_all('li',class_='onLine')
	# print(items2)
	for item2 in items2:
	    href=item2.a.attrs['href']
	    href="http://www.kuwo.cn"+href
	    urls.add(href)
	    # print("http://www.kuwo.cn"+href)

# print(urls)
# 把url 写入文件
def makeUrl(urls):
	url=''
	for url1 in urls:
		url=url+url1+"\r\n"
	with open(name+"/url.txt","w") as f:
		f.write(url)

makeUrl(urls)
