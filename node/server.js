import express from 'express';
import crypto from 'crypto';
import { fetch } from 'undici';
import fs from 'fs';
import path from 'path';

const app = express();
const cfg = JSON.parse(fs.readFileSync(path.resolve('./config.json')));

function hmac(url, expire, nonce){ return crypto.createHmac('sha256', cfg.secretKey).update(`${url}|${expire}|${nonce}`).digest('hex'); }
function verify(url, expire, nonce, token){ return crypto.timingSafeEqual(Buffer.from(hmac(url,expire,nonce)), Buffer.from(token)); }

app.get('/generate', (req,res)=>{
    const file = req.query.file;
    if(!file) return res.status(400).json({error:'file required'});
    if(cfg.requireAuth && req.query.auth_token !== cfg.authToken) return res.status(403).json({error:'Invalid auth'});

    const nonce = crypto.randomBytes(12).toString('hex');
    const expire = cfg.enableExpiry ? Math.floor(Date.now()/1000) + cfg.expiryTime : 0;
    const token = hmac(file, expire, nonce);
    const download_url = `/download?file=${encodeURIComponent(file)}&nonce=${nonce}&expire=${expire}&token=${token}`;
    res.json({download_url, expire_at: expire?new Date(expire*1000).toISOString():null});
});

app.get('/download', async (req,res)=>{
    const { file, nonce, expire, token } = req.query;
    if(!file||!nonce||!token) return res.status(403).send('Missing parameters');
    const exp = parseInt(expire||0);
    if(cfg.enableExpiry && exp>0 && Math.floor(Date.now()/1000)>exp) return res.status(403).send('Expired');
    if(!verify(file, exp, nonce, token)) return res.status(403).send('Invalid token');

    try{
        const r = await fetch(file, { method:'GET', headers:{'accept-encoding':'identity'} });
        res.status(r.status);
        r.headers.forEach((v,k)=>res.setHeader(k,v));
        if(cfg.limitSpeed && cfg.speedKB>0){
            const reader = r.body.getReader();
            const sleep = ms => new Promise(r=>setTimeout(r,ms));
            const chunkSize = Math.min(cfg.speedKB*1024,65536);
            let bytesSent=0;
            while(true){
                const {done,value} = await reader.read();
                if(done) break;
                let offset=0;
                while(offset<value.length){
                    const slice = value.subarray(offset, offset+chunkSize);
                    res.write(Buffer.from(slice));
                    bytesSent+=slice.length;
                    offset+=slice.length;
                    await sleep(Math.ceil(slice.length/(cfg.speedKB*1024)*1000));
                    if(cfg.maxFileMB>0 && bytesSent>cfg.maxFileMB*1024*1024){ res.end(); return; }
                }
            }
            res.end();
        } else { r.body.pipeTo(res); }
    }catch(e){ res.status(502).send('Upstream error'); }
});

app.listen(cfg.port, ()=>console.log(`Node Download Proxy running on port ${cfg.port}`));
